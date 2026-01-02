<?php

namespace Tests\Property;

use App\Http\Controllers\TeacherMonitoringController;
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
 * Property-based tests for attendance statistics accuracy.
 * **Feature: qr-attendance-laravel-migration**
 */
class AttendanceStatisticsPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected TeacherAttendanceService $teacherAttendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
        $this->teacherAttendanceService = new TeacherAttendanceService();
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
     * Helper to create base test data.
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
            'full_name' => 'Admin User',
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
     * **Feature: qr-attendance-laravel-migration, Property 38: Attendance statistics accuracy**
     * **Validates: Requirements 11.3**
     * 
     * For any set of teacher_attendance records matching filter criteria, the statistics
     * SHALL accurately reflect: count where attendance_status='confirmed', count where
     * attendance_status='late', count where attendance_status='pending', count where
     * attendance_status='absent'.
     */
    public function testAttendanceStatisticsAccuracy(): void
    {
        $this->forAll(
            Generator\choose(0, 10),  // confirmed count
            Generator\choose(0, 10),  // late count
            Generator\choose(0, 10),  // pending count
            Generator\choose(0, 10),  // absent count
            Generator\choose(0, 5)    // no_scan count
        )
        ->withMaxSize(100)
        ->then(function ($confirmedCount, $lateCount, $pendingCount, $absentCount, $noScanCount) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $baseData = $this->createBaseTestData();
            $schoolYear = $baseData['schoolYear'];
            $schedule = $baseData['schedule'];

            $expectedCounts = [
                'confirmed' => $confirmedCount,
                'late' => $lateCount,
                'pending' => $pendingCount,
                'absent' => $absentCount,
                'no_scan' => $noScanCount,
            ];

            $teacherIndex = 0;

            // Create teacher attendance records with specific statuses
            foreach ($expectedCounts as $status => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $teacher = User::create([
                        'username' => 'teacher_' . $teacherIndex . '_' . uniqid(),
                        'password' => bcrypt('password'),
                        'role' => 'teacher',
                        'full_name' => 'Teacher ' . $teacherIndex,
                        'is_active' => true,
                    ]);

                    TeacherAttendance::create([
                        'teacher_id' => $teacher->id,
                        'school_year_id' => $schoolYear->id,
                        'attendance_date' => Carbon::today(),
                        'time_in' => $status !== 'absent' ? Carbon::now() : null,
                        'attendance_status' => $status,
                        'late_status' => $status === 'late' ? 'late' : ($status === 'confirmed' ? 'on_time' : null),
                        'time_rule_id' => in_array($status, ['confirmed', 'late']) ? $schedule->id : null,
                    ]);

                    $teacherIndex++;
                }
            }

            // Get attendance records
            $attendances = TeacherAttendance::with(['teacher', 'timeRule'])
                ->today()
                ->forSchoolYear($schoolYear->id)
                ->get();

            // Calculate statistics using the same logic as TeacherMonitoringController
            $stats = [
                'total' => $attendances->count(),
                'confirmed' => $attendances->where('attendance_status', 'confirmed')->count(),
                'late' => $attendances->where('attendance_status', 'late')->count(),
                'pending' => $attendances->where('attendance_status', 'pending')->count(),
                'absent' => $attendances->where('attendance_status', 'absent')->count(),
                'no_scan' => $attendances->where('attendance_status', 'no_scan')->count(),
            ];

            // Verify statistics accuracy
            $this->assertEquals(
                $confirmedCount,
                $stats['confirmed'],
                "Confirmed count should be accurate"
            );

            $this->assertEquals(
                $lateCount,
                $stats['late'],
                "Late count should be accurate"
            );

            $this->assertEquals(
                $pendingCount,
                $stats['pending'],
                "Pending count should be accurate"
            );

            $this->assertEquals(
                $absentCount,
                $stats['absent'],
                "Absent count should be accurate"
            );

            $this->assertEquals(
                $noScanCount,
                $stats['no_scan'],
                "No scan count should be accurate"
            );

            // Verify total equals sum of all statuses
            $expectedTotal = $confirmedCount + $lateCount + $pendingCount + $absentCount + $noScanCount;
            $this->assertEquals(
                $expectedTotal,
                $stats['total'],
                "Total should equal sum of all status counts"
            );
        });
    }

    /**
     * Test statistics with date range filters.
     */
    public function testStatisticsWithDateRangeFilters(): void
    {
        $this->forAll(
            Generator\choose(1, 5),   // records in range
            Generator\choose(1, 5)    // records outside range
        )
        ->withMaxSize(100)
        ->then(function ($inRangeCount, $outOfRangeCount) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $baseData = $this->createBaseTestData();
            $schoolYear = $baseData['schoolYear'];
            $schedule = $baseData['schedule'];

            $teacherIndex = 0;
            $targetDate = Carbon::today();
            $outOfRangeDate = Carbon::today()->subDays(10);

            // Create records within date range (today)
            for ($i = 0; $i < $inRangeCount; $i++) {
                $teacher = User::create([
                    'username' => 'teacher_in_' . $teacherIndex . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Teacher In Range ' . $teacherIndex,
                    'is_active' => true,
                ]);

                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'school_year_id' => $schoolYear->id,
                    'attendance_date' => $targetDate,
                    'time_in' => Carbon::now(),
                    'attendance_status' => 'confirmed',
                    'late_status' => 'on_time',
                    'time_rule_id' => $schedule->id,
                ]);

                $teacherIndex++;
            }

            // Create records outside date range
            for ($i = 0; $i < $outOfRangeCount; $i++) {
                $teacher = User::create([
                    'username' => 'teacher_out_' . $teacherIndex . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Teacher Out Range ' . $teacherIndex,
                    'is_active' => true,
                ]);

                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'school_year_id' => $schoolYear->id,
                    'attendance_date' => $outOfRangeDate,
                    'time_in' => $outOfRangeDate->copy()->setTime(7, 30),
                    'attendance_status' => 'late',
                    'late_status' => 'late',
                    'time_rule_id' => $schedule->id,
                ]);

                $teacherIndex++;
            }

            // Query with date filter (today only)
            $filteredAttendances = TeacherAttendance::whereDate('attendance_date', $targetDate)
                ->forSchoolYear($schoolYear->id)
                ->get();

            // Verify only in-range records are returned
            $this->assertEquals(
                $inRangeCount,
                $filteredAttendances->count(),
                "Filtered query should return only records within date range"
            );

            // Verify statistics for filtered results
            $stats = [
                'confirmed' => $filteredAttendances->where('attendance_status', 'confirmed')->count(),
                'late' => $filteredAttendances->where('attendance_status', 'late')->count(),
            ];

            $this->assertEquals(
                $inRangeCount,
                $stats['confirmed'],
                "All in-range records should be confirmed"
            );

            $this->assertEquals(
                0,
                $stats['late'],
                "No late records should be in today's filter"
            );
        });
    }

    /**
     * Test statistics with teacher filter.
     */
    public function testStatisticsWithTeacherFilter(): void
    {
        $this->forAll(
            Generator\choose(1, 5),   // records for target teacher
            Generator\choose(1, 5)    // records for other teachers
        )
        ->withMaxSize(100)
        ->then(function ($targetTeacherRecords, $otherTeacherRecords) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $baseData = $this->createBaseTestData();
            $schoolYear = $baseData['schoolYear'];
            $schedule = $baseData['schedule'];

            // Create target teacher
            $targetTeacher = User::create([
                'username' => 'target_teacher_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Target Teacher',
                'is_active' => true,
            ]);

            // Create records for target teacher (on different days)
            for ($i = 0; $i < $targetTeacherRecords; $i++) {
                TeacherAttendance::create([
                    'teacher_id' => $targetTeacher->id,
                    'school_year_id' => $schoolYear->id,
                    'attendance_date' => Carbon::today()->subDays($i),
                    'time_in' => Carbon::now(),
                    'attendance_status' => 'confirmed',
                    'late_status' => 'on_time',
                    'time_rule_id' => $schedule->id,
                ]);
            }

            // Create records for other teachers
            for ($i = 0; $i < $otherTeacherRecords; $i++) {
                $otherTeacher = User::create([
                    'username' => 'other_teacher_' . $i . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Other Teacher ' . $i,
                    'is_active' => true,
                ]);

                TeacherAttendance::create([
                    'teacher_id' => $otherTeacher->id,
                    'school_year_id' => $schoolYear->id,
                    'attendance_date' => Carbon::today()->subDays($i),
                    'time_in' => Carbon::now(),
                    'attendance_status' => 'late',
                    'late_status' => 'late',
                    'time_rule_id' => $schedule->id,
                ]);
            }

            // Query with teacher filter
            $filteredAttendances = $this->teacherAttendanceService->getAttendanceRecords([
                'teacher_id' => $targetTeacher->id,
                'school_year_id' => $schoolYear->id,
            ]);

            // Verify only target teacher's records are returned
            $this->assertEquals(
                $targetTeacherRecords,
                $filteredAttendances->count(),
                "Filtered query should return only target teacher's records"
            );

            // Verify all returned records belong to target teacher
            foreach ($filteredAttendances as $attendance) {
                $this->assertEquals(
                    $targetTeacher->id,
                    $attendance->teacher_id,
                    "All returned records should belong to target teacher"
                );
            }
        });
    }
}
