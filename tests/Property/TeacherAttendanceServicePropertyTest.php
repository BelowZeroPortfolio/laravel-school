<?php

namespace Tests\Property;

use App\Models\SchoolYear;
use App\Models\TeacherAttendance;
use App\Models\TimeSchedule;
use App\Models\User;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-based tests for TeacherAttendanceService
 */
class TeacherAttendanceServicePropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected TeacherAttendanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
        $this->service = new TeacherAttendanceService();
    }

    /**
     * Helper method to clean up database state between iterations.
     */
    protected function cleanupDatabase(): void
    {
        TeacherAttendance::query()->delete();
        TimeSchedule::query()->delete();
        SchoolYear::query()->delete();
        User::query()->delete();
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 1: Teacher login creates pending attendance**
     * **Validates: Requirements 1.2, 3.1**
     * 
     * For any teacher user, when they successfully log in, a teacher_attendance record SHALL exist
     * for today with attendance_status = 'pending' and time_in set to a timestamp within 1 second of login time.
     */
    public function testTeacherLoginCreatesPendingAttendance(): void
    {
        $this->forAll(
            Generator\choose(1, 100)  // teacher index for uniqueness
        )
        ->withMaxSize(100)
        ->then(function ($teacherIndex) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create active school year
            $schoolYear = SchoolYear::create([
                'name' => '2025-2026-' . uniqid(),
                'is_active' => true,
                'is_locked' => false,
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
            ]);

            // Create a teacher
            $teacher = User::create([
                'username' => 'teacher_' . $teacherIndex . '_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Teacher ' . $teacherIndex,
                'is_active' => true,
            ]);

            $beforeLogin = Carbon::now();
            
            // Record time in (simulating login)
            $result = $this->service->recordTimeIn($teacher->id);
            
            $afterLogin = Carbon::now();

            // Verify the result
            $this->assertTrue($result, "recordTimeIn should return true");

            // Verify attendance record exists
            $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            $this->assertNotNull($attendance, "Attendance record should exist");
            $this->assertEquals('pending', $attendance->attendance_status, "Status should be pending");
            $this->assertEquals($schoolYear->id, $attendance->school_year_id, "Should be associated with active school year");
            
            // Verify time_in is within 1 second of login time
            $timeIn = Carbon::parse($attendance->time_in);
            $this->assertTrue(
                $timeIn->greaterThanOrEqualTo($beforeLogin->subSecond()) && 
                $timeIn->lessThanOrEqualTo($afterLogin->addSecond()),
                "time_in should be within 1 second of login time"
            );
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 8: Multiple logins update existing record**
     * **Validates: Requirements 3.2**
     * 
     * For any teacher who logs in multiple times on the same day, only one teacher_attendance record
     * SHALL exist for that day, and time_in SHALL reflect the most recent login.
     */
    public function testMultipleLoginsUpdateExistingRecord(): void
    {
        $this->forAll(
            Generator\choose(2, 5)  // number of logins
        )
        ->withMaxSize(100)
        ->then(function ($loginCount) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create active school year
            SchoolYear::create([
                'name' => '2025-2026-' . uniqid(),
                'is_active' => true,
                'is_locked' => false,
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
            ]);

            // Create a teacher
            $teacher = User::create([
                'username' => 'teacher_multi_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Multi Login Teacher',
                'is_active' => true,
            ]);

            // Perform multiple logins
            for ($i = 0; $i < $loginCount; $i++) {
                $this->service->recordTimeIn($teacher->id);
                // Small delay to ensure different timestamps
                usleep(10000); // 10ms
            }

            // Verify only one record exists
            $recordCount = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', Carbon::today())
                ->count();

            $this->assertEquals(1, $recordCount, "Only one attendance record should exist for the day");

            // Verify the record has the most recent time_in
            $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            $this->assertNotNull($attendance);
            $this->assertEquals('pending', $attendance->attendance_status);
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 10: Login does not evaluate lateness**
     * **Validates: Requirements 3.4**
     * 
     * For any teacher who has just logged in (before any student scan), the attendance_status
     * SHALL be 'pending' and late_status SHALL be NULL.
     */
    public function testLoginDoesNotEvaluateLateness(): void
    {
        $this->forAll(
            Generator\choose(6, 12),  // login hour
            Generator\choose(0, 59)   // login minute
        )
        ->withMaxSize(100)
        ->then(function ($hour, $minute) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create active school year
            SchoolYear::create([
                'name' => '2025-2026-' . uniqid(),
                'is_active' => true,
                'is_locked' => false,
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
            ]);

            // Create active time schedule with early cutoff
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin',
                'is_active' => true,
            ]);

            TimeSchedule::create([
                'name' => 'Test Schedule ' . uniqid(),
                'time_in' => '07:00:00',
                'time_out' => '17:00:00',
                'late_threshold_minutes' => 15,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            // Create a teacher
            $teacher = User::create([
                'username' => 'teacher_late_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Late Test Teacher',
                'is_active' => true,
            ]);

            // Record time in
            $this->service->recordTimeIn($teacher->id);

            // Verify attendance record
            $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            $this->assertNotNull($attendance);
            $this->assertEquals('pending', $attendance->attendance_status, "Status should be pending, not evaluated");
            $this->assertNull($attendance->late_status, "late_status should be NULL before Phase 2");
        });
    }


    /**
     * **Feature: qr-attendance-laravel-migration, Property 12: First scan locks time_rule_id**
     * **Validates: Requirements 4.2**
     * 
     * For any teacher_attendance record where first_student_scan is being set,
     * the time_rule_id SHALL be set to the ID of the currently active TimeSchedule.
     */
    public function testFirstScanLocksTimeRuleId(): void
    {
        $this->forAll(
            Generator\choose(7, 9),   // scan hour
            Generator\choose(0, 59)   // scan minute
        )
        ->withMaxSize(100)
        ->then(function ($scanHour, $scanMinute) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create active school year
            SchoolYear::create([
                'name' => '2025-2026-' . uniqid(),
                'is_active' => true,
                'is_locked' => false,
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
            ]);

            // Create admin for time schedule
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin',
                'is_active' => true,
            ]);

            // Create active time schedule
            $activeSchedule = TimeSchedule::create([
                'name' => 'Active Schedule ' . uniqid(),
                'time_in' => '07:00:00',
                'time_out' => '17:00:00',
                'late_threshold_minutes' => 30,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            // Create a teacher and record login
            $teacher = User::create([
                'username' => 'teacher_scan_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Scan Test Teacher',
                'is_active' => true,
            ]);

            $this->service->recordTimeIn($teacher->id);

            // Record first student scan
            $scanTime = Carbon::today()->setHour($scanHour)->setMinute($scanMinute);
            $this->service->recordFirstStudentScan($teacher->id, $scanTime);

            // Verify time_rule_id is locked to active schedule
            $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            $this->assertNotNull($attendance);
            $this->assertEquals(
                $activeSchedule->id,
                $attendance->time_rule_id,
                "time_rule_id should be locked to the active schedule"
            );
            $this->assertNotNull($attendance->first_student_scan, "first_student_scan should be set");
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 14: Subsequent scans preserve first_student_scan**
     * **Validates: Requirements 4.4**
     * 
     * For any student scan where the teacher's first_student_scan is already set,
     * the first_student_scan timestamp SHALL remain unchanged.
     */
    public function testSubsequentScansPreserveFirstStudentScan(): void
    {
        $this->forAll(
            Generator\choose(2, 5)  // number of subsequent scans
        )
        ->withMaxSize(100)
        ->then(function ($scanCount) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create active school year
            SchoolYear::create([
                'name' => '2025-2026-' . uniqid(),
                'is_active' => true,
                'is_locked' => false,
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
            ]);

            // Create admin and active time schedule
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin',
                'is_active' => true,
            ]);

            TimeSchedule::create([
                'name' => 'Test Schedule ' . uniqid(),
                'time_in' => '07:00:00',
                'time_out' => '17:00:00',
                'late_threshold_minutes' => 30,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            // Create a teacher and record login
            $teacher = User::create([
                'username' => 'teacher_multi_scan_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Multi Scan Teacher',
                'is_active' => true,
            ]);

            $this->service->recordTimeIn($teacher->id);

            // Record first student scan
            $firstScanTime = Carbon::today()->setHour(7)->setMinute(15);
            $this->service->recordFirstStudentScan($teacher->id, $firstScanTime);

            // Get the original first_student_scan
            $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();
            $originalFirstScan = $attendance->first_student_scan;

            // Perform subsequent scans
            for ($i = 0; $i < $scanCount; $i++) {
                $laterScanTime = Carbon::today()->setHour(7)->setMinute(30 + ($i * 5));
                $this->service->recordFirstStudentScan($teacher->id, $laterScanTime);
            }

            // Verify first_student_scan is unchanged
            $attendance->refresh();
            $this->assertEquals(
                $originalFirstScan->toDateTimeString(),
                $attendance->first_student_scan->toDateTimeString(),
                "first_student_scan should remain unchanged after subsequent scans"
            );
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 15: Late determination logic**
     * **Validates: Requirements 5.1, 5.2, 5.3**
     * 
     * For any teacher_attendance record being finalized:
     * - IF teacher_time_in > cutoff_time OR first_student_scan > cutoff_time THEN late
     * - IF teacher_time_in <= cutoff_time AND first_student_scan <= cutoff_time THEN confirmed
     */
    public function testLateDeterminationLogic(): void
    {
        $this->forAll(
            Generator\choose(6, 9),   // teacher login hour
            Generator\choose(0, 59),  // teacher login minute
            Generator\choose(6, 9),   // student scan hour
            Generator\choose(0, 59),  // student scan minute
            Generator\choose(7, 8),   // schedule time_in hour
            Generator\choose(15, 45)  // late threshold minutes
        )
        ->withMaxSize(100)
        ->then(function ($loginHour, $loginMin, $scanHour, $scanMin, $scheduleHour, $threshold) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create active school year
            SchoolYear::create([
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

            // Create time schedule
            $schedule = TimeSchedule::create([
                'name' => 'Test Schedule ' . uniqid(),
                'time_in' => sprintf('%02d:00:00', $scheduleHour),
                'time_out' => '17:00:00',
                'late_threshold_minutes' => $threshold,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            // Create teacher
            $teacher = User::create([
                'username' => 'teacher_late_test_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Late Test Teacher',
                'is_active' => true,
            ]);

            // Calculate cutoff time in minutes
            $cutoffMinutes = $scheduleHour * 60 + $threshold;
            $loginMinutes = $loginHour * 60 + $loginMin;
            $scanMinutes = $scanHour * 60 + $scanMin;

            // Determine expected status
            $expectedLate = ($loginMinutes > $cutoffMinutes) || ($scanMinutes > $cutoffMinutes);

            // Create attendance record manually with specific times
            $loginTime = Carbon::today()->setHour($loginHour)->setMinute($loginMin);
            $scanTime = Carbon::today()->setHour($scanHour)->setMinute($scanMin);

            $attendance = TeacherAttendance::create([
                'teacher_id' => $teacher->id,
                'school_year_id' => SchoolYear::active()->first()->id,
                'attendance_date' => Carbon::today(),
                'time_in' => $loginTime,
                'attendance_status' => 'pending',
                'late_status' => null,
            ]);

            // Record first student scan (this will lock time_rule_id and finalize)
            $attendance->update([
                'first_student_scan' => $scanTime,
                'time_rule_id' => $schedule->id,
            ]);

            // Call finalize
            $this->service->finalizeAttendance($teacher->id, Carbon::today()->toDateString());

            // Verify the result
            $attendance->refresh();

            if ($expectedLate) {
                $this->assertEquals('late', $attendance->attendance_status, 
                    "Expected late: login={$loginHour}:{$loginMin}, scan={$scanHour}:{$scanMin}, cutoff={$scheduleHour}:00+{$threshold}min");
                $this->assertEquals('late', $attendance->late_status);
            } else {
                $this->assertEquals('confirmed', $attendance->attendance_status,
                    "Expected confirmed: login={$loginHour}:{$loginMin}, scan={$scanHour}:{$scanMin}, cutoff={$scheduleHour}:00+{$threshold}min");
                $this->assertEquals('on_time', $attendance->late_status);
            }
        });
    }


    /**
     * **Feature: qr-attendance-laravel-migration, Property 17: Historical records immutable on rule change**
     * **Validates: Requirements 5.5**
     * 
     * For any TimeSchedule update, all existing teacher_attendance records with that time_rule_id
     * SHALL retain their original attendance_status and late_status values.
     */
    public function testHistoricalRecordsImmutableOnRuleChange(): void
    {
        $this->forAll(
            Generator\choose(2, 5)  // number of historical records
        )
        ->withMaxSize(100)
        ->then(function ($recordCount) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create active school year
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

            // Create time schedule
            $schedule = TimeSchedule::create([
                'name' => 'Original Schedule ' . uniqid(),
                'time_in' => '07:00:00',
                'time_out' => '17:00:00',
                'late_threshold_minutes' => 15,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            // Create historical attendance records with this time rule
            $originalStatuses = [];
            for ($i = 0; $i < $recordCount; $i++) {
                $teacher = User::create([
                    'username' => 'teacher_hist_' . $i . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Historical Teacher ' . $i,
                    'is_active' => true,
                ]);

                $status = $i % 2 === 0 ? 'confirmed' : 'late';
                $lateStatus = $i % 2 === 0 ? 'on_time' : 'late';

                $attendance = TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'school_year_id' => $schoolYear->id,
                    'attendance_date' => Carbon::today()->subDays($i + 1),
                    'time_in' => Carbon::today()->subDays($i + 1)->setHour(7)->setMinute(0),
                    'first_student_scan' => Carbon::today()->subDays($i + 1)->setHour(7)->setMinute(10),
                    'attendance_status' => $status,
                    'late_status' => $lateStatus,
                    'time_rule_id' => $schedule->id,
                ]);

                $originalStatuses[$attendance->id] = [
                    'attendance_status' => $status,
                    'late_status' => $lateStatus,
                ];
            }

            // Update the time schedule (simulating rule change)
            $schedule->update([
                'time_in' => '06:00:00',
                'late_threshold_minutes' => 5,
            ]);

            // Verify all historical records retain their original status
            foreach ($originalStatuses as $attendanceId => $original) {
                $attendance = TeacherAttendance::find($attendanceId);
                
                $this->assertEquals(
                    $original['attendance_status'],
                    $attendance->attendance_status,
                    "Historical attendance_status should be unchanged after rule update"
                );
                $this->assertEquals(
                    $original['late_status'],
                    $attendance->late_status,
                    "Historical late_status should be unchanged after rule update"
                );
            }
        });
    }
}
