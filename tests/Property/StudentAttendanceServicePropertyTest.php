<?php

namespace Tests\Property;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeacherAttendance;
use App\Models\TimeSchedule;
use App\Models\User;
use App\Services\StudentAttendanceService;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-based tests for StudentAttendanceService
 */
class StudentAttendanceServicePropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected StudentAttendanceService $service;
    protected TeacherAttendanceService $teacherAttendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
        $this->teacherAttendanceService = new TeacherAttendanceService();
        $this->service = new StudentAttendanceService($this->teacherAttendanceService);
    }

    /**
     * Helper method to clean up database state between iterations.
     */
    protected function cleanupDatabase(): void
    {
        Attendance::query()->delete();
        TeacherAttendance::query()->delete();
        // Delete pivot table entries
        \DB::table('student_classes')->delete();
        ClassRoom::query()->delete();
        Student::query()->delete();
        TimeSchedule::query()->delete();
        SchoolYear::query()->delete();
        User::query()->delete();
    }

    /**
     * Helper to create base test data (school year, admin, time schedule).
     */
    protected function createBaseTestData(): array
    {
        $schoolYear = SchoolYear::create([
            'name' => '2025-2026-' . uniqid(),
            'is_active' => true,
            'is_locked' => false,
            'start_date' => '2025-06-01',
            'end_date' => '2026-03-31',
        ]);

        $admin = User::create([
            'username' => 'admin_' . uniqid(),
            'password' => bcrypt('password'),
            'role' => 'admin',
            'full_name' => 'Admin',
            'is_active' => true,
        ]);

        $schedule = TimeSchedule::create([
            'name' => 'Test Schedule ' . uniqid(),
            'time_in' => '07:00:00',
            'time_out' => '17:00:00',
            'late_threshold_minutes' => 30,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        return compact('schoolYear', 'admin', 'schedule');
    }


    /**
     * **Feature: qr-attendance-laravel-migration, Property 18: Valid QR code creates attendance**
     * **Validates: Requirements 6.1**
     * 
     * For any valid student QR code (matching LRN or student_id), scanning SHALL create
     * an attendance record with check_in_time set to the current timestamp.
     */
    public function testValidQRCodeCreatesAttendance(): void
    {
        $this->forAll(
            Generator\choose(1, 100)  // student index for uniqueness
        )
        ->withMaxSize(100)
        ->then(function ($studentIndex) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $this->createBaseTestData();

            // Create a student with unique LRN
            $lrn = str_pad($studentIndex, 12, '0', STR_PAD_LEFT);
            $student = Student::create([
                'student_id' => 'STU-' . uniqid(),
                'lrn' => $lrn,
                'first_name' => 'Test',
                'last_name' => 'Student ' . $studentIndex,
                'is_active' => true,
            ]);

            $beforeScan = Carbon::now();

            // Process QR code scan using LRN
            $result = $this->service->processQRCodeScan($lrn);

            $afterScan = Carbon::now();

            // Verify success
            $this->assertTrue($result['success'], "Scan should succeed for valid QR code");

            // Verify attendance record exists
            $attendance = Attendance::where('student_id', $student->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            $this->assertNotNull($attendance, "Attendance record should exist");
            
            // Verify check_in_time is within expected range
            $checkInTime = Carbon::parse($attendance->check_in_time);
            $this->assertTrue(
                $checkInTime->greaterThanOrEqualTo($beforeScan->subSecond()) &&
                $checkInTime->lessThanOrEqualTo($afterScan->addSecond()),
                "check_in_time should be within 1 second of scan time"
            );
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 19: QR code lookup priority**
     * **Validates: Requirements 6.2**
     * 
     * For any QR code that matches both an LRN and a different student's student_id,
     * the student with the matching LRN SHALL be returned.
     */
    public function testQRCodeLookupPriority(): void
    {
        $this->forAll(
            Generator\choose(1, 100)  // test index for uniqueness
        )
        ->withMaxSize(100)
        ->then(function ($testIndex) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $this->createBaseTestData();

            // Create a shared QR code value
            $sharedCode = 'SHARED' . str_pad($testIndex, 6, '0', STR_PAD_LEFT);

            // Create student 1 with the shared code as LRN
            $studentWithLrn = Student::create([
                'student_id' => 'STU-LRN-' . uniqid(),
                'lrn' => $sharedCode,
                'first_name' => 'LRN',
                'last_name' => 'Student',
                'is_active' => true,
            ]);

            // Create student 2 with the shared code as student_id
            $studentWithId = Student::create([
                'student_id' => $sharedCode,
                'lrn' => str_pad($testIndex + 1000, 12, '0', STR_PAD_LEFT),
                'first_name' => 'ID',
                'last_name' => 'Student',
                'is_active' => true,
            ]);

            // Find student by the shared code
            $foundStudent = $this->service->findStudentByQRCode($sharedCode);

            // Verify LRN match takes priority
            $this->assertNotNull($foundStudent, "Should find a student");
            $this->assertEquals(
                $studentWithLrn->id,
                $foundStudent->id,
                "LRN match should take priority over student_id match"
            );
        });
    }


    /**
     * **Feature: qr-attendance-laravel-migration, Property 20: Duplicate scan prevention**
     * **Validates: Requirements 6.3**
     * 
     * For any student who has already scanned today, a subsequent scan SHALL NOT create
     * a new attendance record and SHALL return an appropriate error.
     */
    public function testDuplicateScanPrevention(): void
    {
        $this->forAll(
            Generator\choose(2, 5)  // number of duplicate scan attempts
        )
        ->withMaxSize(100)
        ->then(function ($scanAttempts) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $this->createBaseTestData();

            // Create a student
            $lrn = str_pad(rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
            $student = Student::create([
                'student_id' => 'STU-' . uniqid(),
                'lrn' => $lrn,
                'first_name' => 'Test',
                'last_name' => 'Student',
                'is_active' => true,
            ]);

            // First scan should succeed
            $firstResult = $this->service->processQRCodeScan($lrn);
            $this->assertTrue($firstResult['success'], "First scan should succeed");

            // Get the original attendance record
            $originalAttendance = Attendance::where('student_id', $student->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();
            $originalId = $originalAttendance->id;

            // Subsequent scans should fail
            for ($i = 0; $i < $scanAttempts; $i++) {
                $result = $this->service->processQRCodeScan($lrn);
                
                $this->assertFalse($result['success'], "Duplicate scan should fail");
                $this->assertEquals('DUPLICATE_SCAN', $result['error_code'], "Error code should be DUPLICATE_SCAN");
            }

            // Verify only one attendance record exists
            $recordCount = Attendance::where('student_id', $student->id)
                ->whereDate('attendance_date', Carbon::today())
                ->count();

            $this->assertEquals(1, $recordCount, "Only one attendance record should exist");

            // Verify it's the same record
            $currentAttendance = Attendance::where('student_id', $student->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();
            $this->assertEquals($originalId, $currentAttendance->id, "Attendance record should be unchanged");
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 21: Auto-calculate student late status**
     * **Validates: Requirements 6.4**
     * 
     * For any student attendance record, if check_in_time > (active_schedule.time_in + late_threshold_minutes),
     * status SHALL be 'late'; otherwise status SHALL be 'present'.
     */
    public function testAutoCalculateStudentLateStatus(): void
    {
        $this->forAll(
            Generator\choose(6, 10),  // check-in hour
            Generator\choose(0, 59),  // check-in minute
            Generator\choose(7, 8),   // schedule time_in hour
            Generator\choose(15, 45)  // late threshold minutes
        )
        ->withMaxSize(100)
        ->then(function ($checkInHour, $checkInMin, $scheduleHour, $threshold) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create school year
            $schoolYear = SchoolYear::create([
                'name' => '2025-2026-' . uniqid(),
                'is_active' => true,
                'is_locked' => false,
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
            ]);

            // Create admin
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin',
                'is_active' => true,
            ]);

            // Create time schedule with specific parameters
            TimeSchedule::create([
                'name' => 'Test Schedule ' . uniqid(),
                'time_in' => sprintf('%02d:00:00', $scheduleHour),
                'time_out' => '17:00:00',
                'late_threshold_minutes' => $threshold,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            // Calculate expected status
            $cutoffMinutes = $scheduleHour * 60 + $threshold;
            $checkInMinutes = $checkInHour * 60 + $checkInMin;
            $expectedLate = $checkInMinutes > $cutoffMinutes;

            // Test calculateLateStatus directly
            $checkTime = sprintf('%02d:%02d:00', $checkInHour, $checkInMin);
            $result = $this->service->calculateLateStatus($checkTime);

            if ($expectedLate) {
                $this->assertEquals('late', $result['status'],
                    "Expected late: check-in={$checkInHour}:{$checkInMin}, cutoff={$scheduleHour}:00+{$threshold}min");
                $this->assertTrue($result['is_late']);
            } else {
                $this->assertEquals('present', $result['status'],
                    "Expected present: check-in={$checkInHour}:{$checkInMin}, cutoff={$scheduleHour}:00+{$threshold}min");
                $this->assertFalse($result['is_late']);
            }
        });
    }


    /**
     * **Feature: qr-attendance-laravel-migration, Property 22: Student scan triggers teacher Phase 2**
     * **Validates: Requirements 6.5**
     * 
     * For any student scan where the student is enrolled in a class whose teacher has a pending
     * attendance record with NULL first_student_scan, the teacher's first_student_scan SHALL be set.
     */
    public function testStudentScanTriggersTeacherPhase2(): void
    {
        $this->forAll(
            Generator\choose(1, 50)  // test index for uniqueness
        )
        ->withMaxSize(100)
        ->then(function ($testIndex) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $baseData = $this->createBaseTestData();
            $schoolYear = $baseData['schoolYear'];

            // Create a teacher
            $teacher = User::create([
                'username' => 'teacher_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Test Teacher',
                'is_active' => true,
            ]);

            // Create teacher's pending attendance record (Phase 1 complete)
            $this->teacherAttendanceService->recordTimeIn($teacher->id);

            // Verify teacher has pending attendance with no first_student_scan
            $teacherAttendanceBefore = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();
            $this->assertEquals('pending', $teacherAttendanceBefore->attendance_status);
            $this->assertNull($teacherAttendanceBefore->first_student_scan);

            // Create a class assigned to the teacher
            $class = ClassRoom::create([
                'grade_level' => 'Grade ' . ($testIndex % 12 + 1),
                'section' => chr(65 + ($testIndex % 6)),  // A-F
                'teacher_id' => $teacher->id,
                'school_year_id' => $schoolYear->id,
                'is_active' => true,
                'max_capacity' => 40,
            ]);

            // Create a student
            $lrn = str_pad($testIndex, 12, '0', STR_PAD_LEFT);
            $student = Student::create([
                'student_id' => 'STU-' . uniqid(),
                'lrn' => $lrn,
                'first_name' => 'Test',
                'last_name' => 'Student',
                'is_active' => true,
            ]);

            // Enroll student in the class
            $class->students()->attach($student->id, [
                'enrolled_at' => now(),
                'is_active' => true,
                'enrollment_type' => 'regular',
                'enrollment_status' => 'enrolled',
            ]);

            $beforeScan = Carbon::now();

            // Process student QR code scan
            $result = $this->service->processQRCodeScan($lrn);

            $afterScan = Carbon::now();

            // Verify scan succeeded
            $this->assertTrue($result['success'], "Student scan should succeed");

            // Verify teacher's first_student_scan is now set (Phase 2 triggered)
            $teacherAttendanceAfter = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            $this->assertNotNull(
                $teacherAttendanceAfter->first_student_scan,
                "Teacher's first_student_scan should be set after student scan"
            );

            // Verify the timestamp is within expected range
            $firstScan = Carbon::parse($teacherAttendanceAfter->first_student_scan);
            $this->assertTrue(
                $firstScan->greaterThanOrEqualTo($beforeScan->subSecond()) &&
                $firstScan->lessThanOrEqualTo($afterScan->addSecond()),
                "first_student_scan should be within 1 second of student scan time"
            );

            // Verify attendance status is no longer pending (finalized)
            $this->assertNotEquals(
                'pending',
                $teacherAttendanceAfter->attendance_status,
                "Teacher attendance should be finalized (not pending)"
            );
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 23: Invalid QR code creates no records**
     * **Validates: Requirements 6.6**
     * 
     * For any QR code that does not match any student's LRN or student_id,
     * no attendance record SHALL be created.
     */
    public function testInvalidQRCodeCreatesNoRecords(): void
    {
        $this->forAll(
            Generator\suchThat(
                fn($code) => strlen($code) > 0,
                Generator\string()
            )
        )
        ->withMaxSize(100)
        ->then(function ($invalidCode) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $this->createBaseTestData();

            // Create a student with different identifiers
            Student::create([
                'student_id' => 'STU-VALID-' . uniqid(),
                'lrn' => '999999999999',
                'first_name' => 'Valid',
                'last_name' => 'Student',
                'is_active' => true,
            ]);

            // Ensure the invalid code doesn't match any student
            $existingStudent = Student::where('lrn', $invalidCode)
                ->orWhere('student_id', $invalidCode)
                ->first();

            if ($existingStudent) {
                // Skip this iteration if by chance the random code matches
                return;
            }

            $attendanceCountBefore = Attendance::count();

            // Process invalid QR code
            $result = $this->service->processQRCodeScan($invalidCode);

            // Verify failure
            $this->assertFalse($result['success'], "Invalid QR code should fail");
            $this->assertEquals('STUDENT_NOT_FOUND', $result['error_code']);

            // Verify no new attendance records created
            $attendanceCountAfter = Attendance::count();
            $this->assertEquals(
                $attendanceCountBefore,
                $attendanceCountAfter,
                "No attendance records should be created for invalid QR code"
            );
        });
    }
}
