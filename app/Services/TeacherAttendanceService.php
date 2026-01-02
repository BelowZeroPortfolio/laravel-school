<?php

namespace App\Services;

use App\Events\AttendanceFinalized;
use App\Events\TeacherLoggedIn;
use App\Models\SchoolYear;
use App\Models\TeacherAttendance;
use App\Models\TimeSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherAttendanceService
{
    /**
     * Record teacher time_in when they log in.
     * Creates a new record or updates existing pending record for today.
     * (Requirements 1.2, 3.1, 3.2, 3.3)
     *
     * @param int $teacherId The teacher's user ID
     * @param int|null $schoolYearId Optional school year ID (defaults to active)
     * @return bool True if successful
     */
    public function recordTimeIn(int $teacherId, ?int $schoolYearId = null): bool
    {
        // Get active school year if not provided (Requirement 3.3)
        if ($schoolYearId === null) {
            $activeSchoolYear = SchoolYear::active()->first();
            $schoolYearId = $activeSchoolYear?->id;
        }

        if ($schoolYearId === null) {
            return false;
        }

        $today = Carbon::today();
        $now = Carbon::now();

        // Check for existing record today (Requirement 3.2)
        $existingRecord = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existingRecord) {
            // Update existing record's time_in (Requirement 3.2)
            $existingRecord->update([
                'time_in' => $now,
            ]);

            // Dispatch TeacherLoggedIn event (Requirement 13.2)
            $teacher = User::find($teacherId);
            if ($teacher) {
                event(new TeacherLoggedIn($teacher, $existingRecord->fresh()));
            }

            return true;
        }

        // Create new record with pending status (Requirement 3.1)
        $attendance = TeacherAttendance::create([
            'teacher_id' => $teacherId,
            'school_year_id' => $schoolYearId,
            'attendance_date' => $today,
            'time_in' => $now,
            'attendance_status' => 'pending',
            'late_status' => null, // Not evaluated until Phase 2 (Requirement 3.4)
        ]);

        // Dispatch TeacherLoggedIn event (Requirement 13.2)
        $teacher = User::find($teacherId);
        if ($teacher) {
            event(new TeacherLoggedIn($teacher, $attendance));
        }

        return true;
    }

    /**
     * Record teacher time_out when they log out.
     * (Requirement 1.3)
     *
     * @param int $teacherId The teacher's user ID
     * @return bool True if successful
     */
    public function recordTimeOut(int $teacherId): bool
    {
        $today = Carbon::today();
        $now = Carbon::now();

        $record = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$record) {
            return false;
        }

        $record->update([
            'time_out' => $now,
        ]);

        return true;
    }

    /**
     * Record first student scan for a teacher.
     * Sets first_student_scan, locks time_rule_id, and calls finalizeAttendance().
     * (Requirements 4.1, 4.2, 4.3, 4.4)
     *
     * @param int $teacherId The teacher's user ID
     * @param Carbon $scanTime The timestamp of the student scan
     * @return bool True if successful
     */
    public function recordFirstStudentScan(int $teacherId, Carbon $scanTime): bool
    {
        $today = Carbon::today();

        $record = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$record) {
            return false;
        }

        // If first_student_scan is already set, do not update (Requirement 4.4)
        if ($record->first_student_scan !== null) {
            return true;
        }

        // Get the currently active time schedule to lock (Requirement 4.2)
        $activeSchedule = TimeSchedule::active()->first();

        if (!$activeSchedule) {
            return false;
        }

        // Set first_student_scan and lock time_rule_id (Requirements 4.1, 4.2)
        $record->update([
            'first_student_scan' => $scanTime,
            'time_rule_id' => $activeSchedule->id,
        ]);

        // Trigger finalization (Requirement 4.3)
        $this->finalizeAttendance($teacherId, $today->toDateString());

        return true;
    }

    /**
     * Finalize teacher attendance by applying late determination logic.
     * Uses the locked time_rule_id for calculations.
     * (Requirements 5.1, 5.2, 5.3, 5.4)
     *
     * @param int $teacherId The teacher's user ID
     * @param string $date The attendance date (Y-m-d format)
     * @return void
     */
    public function finalizeAttendance(int $teacherId, string $date): void
    {
        $record = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereDate('attendance_date', $date)
            ->first();

        if (!$record || !$record->time_rule_id || !$record->first_student_scan) {
            return;
        }

        // Store previous status for event
        $previousStatus = $record->attendance_status;

        // Use the locked time rule, not the currently active one (Requirement 5.4)
        $timeRule = TimeSchedule::find($record->time_rule_id);

        if (!$timeRule) {
            return;
        }

        // Calculate cutoff time: time_in + late_threshold_minutes
        $scheduleTimeIn = Carbon::parse($timeRule->time_in);
        $cutoffTime = $scheduleTimeIn->copy()->addMinutes($timeRule->late_threshold_minutes);

        // Get time components for comparison
        $teacherTimeIn = Carbon::parse($record->time_in);
        $firstStudentScan = Carbon::parse($record->first_student_scan);

        // Create comparable time values (using only time portion)
        $teacherTimeInMinutes = $teacherTimeIn->hour * 60 + $teacherTimeIn->minute;
        $firstScanMinutes = $firstStudentScan->hour * 60 + $firstStudentScan->minute;
        $cutoffMinutes = $cutoffTime->hour * 60 + $cutoffTime->minute;

        // Apply late determination logic (Requirements 5.1, 5.2, 5.3)
        // IF teacher_time_in > cutoff_time OR first_student_scan > cutoff_time THEN late
        // IF teacher_time_in <= cutoff_time AND first_student_scan <= cutoff_time THEN confirmed
        if ($teacherTimeInMinutes > $cutoffMinutes || $firstScanMinutes > $cutoffMinutes) {
            $record->update([
                'attendance_status' => 'late',
                'late_status' => 'late',
            ]);
        } else {
            $record->update([
                'attendance_status' => 'confirmed',
                'late_status' => 'on_time',
            ]);
        }

        // Dispatch AttendanceFinalized event (Requirement 13.3)
        // Only dispatch if status changed from pending
        if ($previousStatus === 'pending') {
            event(new AttendanceFinalized($record->fresh()->load('teacher'), $previousStatus));
        }
    }

    /**
     * Mark teachers without any attendance record as absent.
     * Called at end of day (17:30).
     * (Requirements 12.1, 12.3)
     *
     * @param int|null $schoolYearId Optional school year ID (defaults to active)
     * @return int Number of teachers marked absent
     */
    public function markAbsentTeachers(?int $schoolYearId = null): int
    {
        // Get active school year if not provided
        if ($schoolYearId === null) {
            $activeSchoolYear = SchoolYear::active()->first();
            $schoolYearId = $activeSchoolYear?->id;
        }

        if ($schoolYearId === null) {
            return 0;
        }

        $today = Carbon::today();

        // Get all active teachers
        $allTeachers = User::teachers()->active()->pluck('id');

        // Get teachers who already have attendance records today
        $teachersWithAttendance = TeacherAttendance::whereDate('attendance_date', $today)
            ->pluck('teacher_id');

        // Find teachers without any attendance record (Requirement 12.3)
        $teachersToMarkAbsent = $allTeachers->diff($teachersWithAttendance);

        $count = 0;
        foreach ($teachersToMarkAbsent as $teacherId) {
            TeacherAttendance::create([
                'teacher_id' => $teacherId,
                'school_year_id' => $schoolYearId,
                'attendance_date' => $today,
                'time_in' => null,
                'time_out' => null,
                'first_student_scan' => null,
                'attendance_status' => 'absent',
                'late_status' => null,
                'time_rule_id' => null,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Mark teachers with pending status as no_scan.
     * Called at end of day (18:00).
     * (Requirement 12.2)
     *
     * @return int Number of teachers marked as no_scan
     */
    public function markNoScanTeachers(): int
    {
        $today = Carbon::today();

        // Update all pending records to no_scan
        $count = TeacherAttendance::whereDate('attendance_date', $today)
            ->where('attendance_status', 'pending')
            ->update(['attendance_status' => 'no_scan']);

        return $count;
    }

    /**
     * Get attendance records with optional filters.
     *
     * @param array $filters Filter options (teacher_id, school_year_id, date_from, date_to, status)
     * @return Collection
     */
    public function getAttendanceRecords(array $filters): Collection
    {
        $query = TeacherAttendance::query();

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['school_year_id'])) {
            $query->where('school_year_id', $filters['school_year_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('attendance_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('attendance_date', '<=', $filters['date_to']);
        }

        if (isset($filters['status'])) {
            $query->where('attendance_status', $filters['status']);
        }

        return $query->with(['teacher', 'schoolYear', 'timeRule'])->get();
    }

    /**
     * Get attendance summary for a teacher.
     *
     * @param int $teacherId The teacher's user ID
     * @param int|null $schoolYearId Optional school year ID
     * @return array Summary statistics
     */
    public function getSummary(int $teacherId, ?int $schoolYearId): array
    {
        $query = TeacherAttendance::where('teacher_id', $teacherId);

        if ($schoolYearId) {
            $query->where('school_year_id', $schoolYearId);
        }

        $records = $query->get();

        return [
            'total' => $records->count(),
            'confirmed' => $records->where('attendance_status', 'confirmed')->count(),
            'late' => $records->where('attendance_status', 'late')->count(),
            'pending' => $records->where('attendance_status', 'pending')->count(),
            'absent' => $records->where('attendance_status', 'absent')->count(),
            'no_scan' => $records->where('attendance_status', 'no_scan')->count(),
        ];
    }
}
