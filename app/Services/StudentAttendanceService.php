<?php

namespace App\Services;

use App\Events\StudentScanned;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeacherAttendance;
use App\Models\TimeSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class StudentAttendanceService
{
    protected TeacherAttendanceService $teacherAttendanceService;

    /**
     * Cache TTL for active school year and schedule (5 minutes)
     */
    protected const CACHE_TTL = 300;

    public function __construct(TeacherAttendanceService $teacherAttendanceService)
    {
        $this->teacherAttendanceService = $teacherAttendanceService;
    }

    /**
     * Get active school year with caching.
     * Reduces repeated queries during high-volume scanning.
     */
    protected function getActiveSchoolYear(): ?SchoolYear
    {
        return Cache::remember('active_school_year', self::CACHE_TTL, function () {
            return SchoolYear::active()->first();
        });
    }

    /**
     * Get active time schedule with caching.
     * Reduces repeated queries during high-volume scanning.
     */
    protected function getActiveTimeSchedule(): ?TimeSchedule
    {
        return Cache::remember('active_time_schedule', self::CACHE_TTL, function () {
            return TimeSchedule::active()->first();
        });
    }

    /**
     * Find a student by QR code value.
     * Searches by LRN first, then by student_id.
     * (Requirement 6.2)
     *
     * @param  string  $qrCode  The QR code value (LRN or student_id)
     * @return Student|null The found student or null
     */
    public function findStudentByQRCode(string $qrCode): ?Student
    {
        // Search by LRN first (Requirement 6.2 - priority)
        $student = Student::where('lrn', $qrCode)->first();

        if ($student) {
            return $student;
        }

        // Then search by student_id
        return Student::where('student_id', $qrCode)->first();
    }

    /**
     * Check if a student has already recorded attendance today.
     * (Requirement 6.3)
     *
     * @param int $studentId The student's ID
     * @return bool True if attendance exists for today
     */
    public function hasAttendanceToday(int $studentId): bool
    {
        return Attendance::where('student_id', $studentId)
            ->whereDate('attendance_date', Carbon::today())
            ->exists();
    }


    /**
     * Record attendance for a student.
     * Creates attendance record, auto-calculates late status, triggers teacher Phase 2.
     * (Requirements 6.1, 6.4, 6.5)
     *
     * @param  int  $studentId  The student's ID
     * @param  string|null  $status  Optional status override
     * @return array|false Array with attendance data or false on failure
     */
    public function recordAttendance(int $studentId, ?string $status = null): array|false
    {
        // Get active school year with caching (Requirement 10.3)
        $activeSchoolYear = $this->getActiveSchoolYear();
        if (! $activeSchoolYear) {
            return false;
        }

        $now = Carbon::now();
        $today = Carbon::today();

        // Auto-calculate late status if not provided (Requirement 6.4)
        if ($status === null) {
            $lateInfo = $this->calculateLateStatus($now->toTimeString());
            $status = $lateInfo['status'];
        }

        // Create attendance record (Requirement 6.1)
        $attendance = Attendance::create([
            'student_id' => $studentId,
            'school_year_id' => $activeSchoolYear->id,
            'attendance_date' => $today,
            'check_in_time' => $now,
            'status' => $status,
        ]);

        // Trigger teacher Phase 2 if applicable (Requirement 6.5)
        $this->triggerTeacherPhase2($studentId, $now);

        // Dispatch StudentScanned event (Requirement 13.1)
        $student = Student::find($studentId);
        if ($student) {
            event(new StudentScanned($student, $attendance, $status));
        }

        return [
            'attendance' => $attendance,
            'status' => $status,
            'check_in_time' => $now->toDateTimeString(),
        ];
    }

    /**
     * Process a QR code scan.
     * Main entry point combining lookup, duplicate check, and recording.
     * (Requirements 6.1, 6.2, 6.3, 6.6)
     *
     * @param string $qrCode The scanned QR code value
     * @param string $mode The scan mode ('arrival' or 'departure')
     * @return array Result array with success status and message/data
     */
    public function processQRCodeScan(string $qrCode, string $mode = 'arrival'): array
    {
        // Find student by QR code (Requirement 6.2)
        $student = $this->findStudentByQRCode($qrCode);

        if (!$student) {
            // Invalid QR code (Requirement 6.6)
            return [
                'success' => false,
                'message' => 'Student not found. Please verify the QR code.',
                'error_code' => 'STUDENT_NOT_FOUND',
            ];
        }

        if ($mode === 'arrival') {
            // Check for duplicate scan (Requirement 6.3)
            if ($this->hasAttendanceToday($student->id)) {
                return [
                    'success' => false,
                    'message' => 'Attendance already recorded for today.',
                    'error_code' => 'DUPLICATE_SCAN',
                    'student' => $student,
                ];
            }

            // Record attendance (Requirement 6.1)
            $result = $this->recordAttendance($student->id);

            if ($result === false) {
                return [
                    'success' => false,
                    'message' => 'No active school year. Contact administrator.',
                    'error_code' => 'NO_ACTIVE_SCHOOL_YEAR',
                ];
            }

            return [
                'success' => true,
                'message' => 'Attendance recorded successfully.',
                'student' => $student,
                'attendance' => $result['attendance'],
                'status' => $result['status'],
                'check_in_time' => $result['check_in_time'],
            ];
        }

        // Handle departure mode
        $checkoutResult = $this->recordCheckout($student->id);
        
        if (!$checkoutResult['success']) {
            if ($checkoutResult['error_code'] === 'ALREADY_CHECKED_OUT') {
                return [
                    'success' => false,
                    'message' => 'Already checked out at ' . $checkoutResult['check_out_time'],
                    'error_code' => 'ALREADY_CHECKED_OUT',
                    'student' => $student,
                ];
            }
            return [
                'success' => false,
                'message' => 'No check-in record found for today. Student must check in first.',
                'error_code' => 'NO_ATTENDANCE_RECORD',
                'student' => $student,
            ];
        }

        return [
            'success' => true,
            'message' => 'Checkout recorded successfully.',
            'student' => $student,
            'check_out_time' => $checkoutResult['check_out_time'],
        ];
    }


    /**
     * Calculate late status based on check-in time.
     * Compares against active schedule cutoff.
     * (Requirement 6.4)
     *
     * @param  string  $checkTime  The check-in time (H:i:s format)
     * @return array Array with 'status' and 'is_late' keys
     */
    public function calculateLateStatus(string $checkTime): array
    {
        // Use cached active schedule
        $activeSchedule = $this->getActiveTimeSchedule();

        if (! $activeSchedule) {
            // Default to present if no active schedule
            return [
                'status' => 'present',
                'is_late' => false,
            ];
        }

        // Parse times for comparison
        $checkInTime = Carbon::parse($checkTime);
        $scheduleTimeIn = Carbon::parse($activeSchedule->time_in);
        $cutoffTime = $scheduleTimeIn->copy()->addMinutes($activeSchedule->late_threshold_minutes);

        // Compare using time portion only
        $checkInMinutes = $checkInTime->hour * 60 + $checkInTime->minute;
        $cutoffMinutes = $cutoffTime->hour * 60 + $cutoffTime->minute;

        $isLate = $checkInMinutes > $cutoffMinutes;

        return [
            'status' => $isLate ? 'late' : 'present',
            'is_late' => $isLate,
        ];
    }

    /**
     * Record checkout for a student.
     *
     * @param int $studentId The student's ID
     * @return array Result array with success status and data
     */
    public function recordCheckout(int $studentId): array
    {
        $attendance = Attendance::where('student_id', $studentId)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        if (!$attendance) {
            return [
                'success' => false,
                'error_code' => 'NO_ATTENDANCE_RECORD',
            ];
        }

        // Check if already checked out
        if ($attendance->check_out_time !== null) {
            return [
                'success' => false,
                'error_code' => 'ALREADY_CHECKED_OUT',
                'check_out_time' => $attendance->check_out_time->format('h:i A'),
            ];
        }

        $now = Carbon::now();
        $attendance->update([
            'check_out_time' => $now,
        ]);

        return [
            'success' => true,
            'check_out_time' => $now->format('h:i A'),
        ];
    }

    /**
     * Trigger teacher Phase 2 (first student scan) if applicable.
     * (Requirement 6.5)
     *
     * @param int $studentId The student's ID
     * @param Carbon $scanTime The scan timestamp
     * @return void
     */
    protected function triggerTeacherPhase2(int $studentId, Carbon $scanTime): void
    {
        // Get the student's active classes
        $student = Student::with(['classes' => function ($query) {
            $query->where('classes.is_active', true)
                ->wherePivot('is_active', true);
        }])->find($studentId);

        if (!$student || $student->classes->isEmpty()) {
            return;
        }

        // For each class, check if the teacher has a pending attendance record
        foreach ($student->classes as $class) {
            if (!$class->teacher_id) {
                continue;
            }

            // Check if teacher has a pending attendance record with no first_student_scan
            $teacherAttendance = TeacherAttendance::where('teacher_id', $class->teacher_id)
                ->whereDate('attendance_date', Carbon::today())
                ->where('attendance_status', 'pending')
                ->whereNull('first_student_scan')
                ->first();

            if ($teacherAttendance) {
                // Trigger teacher Phase 2
                $this->teacherAttendanceService->recordFirstStudentScan(
                    $class->teacher_id,
                    $scanTime
                );
            }
        }
    }

    /**
     * Get attendance records by date with optional filters.
     *
     * @param string $date The date (Y-m-d format)
     * @param int|null $schoolYearId Optional school year filter
     * @param int|null $classId Optional class filter
     * @param int|null $teacherId Optional teacher filter (for their classes)
     * @return Collection
     */
    public function getAttendanceByDate(
        string $date,
        ?int $schoolYearId = null,
        ?int $classId = null,
        ?int $teacherId = null
    ): Collection {
        $query = Attendance::with(['student', 'schoolYear', 'recorder'])
            ->whereDate('attendance_date', $date);

        if ($schoolYearId) {
            $query->forSchoolYear($schoolYearId);
        }

        if ($classId) {
            $query->forClass($classId);
        }

        if ($teacherId) {
            // Filter by teacher's classes
            $classIds = ClassRoom::where('teacher_id', $teacherId)
                ->where('is_active', true)
                ->pluck('id');

            $query->whereHas('student.classes', function ($q) use ($classIds) {
                $q->whereIn('classes.id', $classIds);
            });
        }

        return $query->get();
    }
}
