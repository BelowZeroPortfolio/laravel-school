<?php

namespace Tests\Property;

use App\Models\SchoolYear;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-based tests for scheduled commands (end-of-day processing)
 */
class ScheduledCommandsPropertyTest extends TestCase
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
        SchoolYear::query()->delete();
        User::query()->delete();
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 39: Mark absent teachers creates correct records**
     * **Validates: Requirements 12.1, 12.3**
     * 
     * For any teacher without a teacher_attendance record for today when markAbsentTeachers() runs,
     * a new record SHALL be created with attendance_status='absent'.
     */
    public function testMarkAbsentTeachersCreatesCorrectRecords(): void
    {
        $this->forAll(
            Generator\choose(1, 10),  // total teachers
            Generator\choose(0, 10)   // teachers with attendance (capped to total)
        )
        ->withMaxSize(100)
        ->then(function ($totalTeachers, $teachersWithAttendance) {
            // Ensure teachersWithAttendance doesn't exceed totalTeachers
            $teachersWithAttendance = min($teachersWithAttendance, $totalTeachers);
            
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

            // Create teachers
            $teachers = [];
            for ($i = 0; $i < $totalTeachers; $i++) {
                $teachers[] = User::create([
                    'username' => 'teacher_' . $i . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Teacher ' . $i,
                    'is_active' => true,
                ]);
            }

            // Create attendance records for some teachers
            $teachersWithRecords = array_slice($teachers, 0, $teachersWithAttendance);
            foreach ($teachersWithRecords as $teacher) {
                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'school_year_id' => $schoolYear->id,
                    'attendance_date' => Carbon::today(),
                    'time_in' => Carbon::now(),
                    'attendance_status' => 'pending',
                    'late_status' => null,
                ]);
            }

            // Calculate expected absent count
            $expectedAbsentCount = $totalTeachers - $teachersWithAttendance;

            // Run markAbsentTeachers
            $markedCount = $this->service->markAbsentTeachers();

            // Verify the count
            $this->assertEquals(
                $expectedAbsentCount,
                $markedCount,
                "Should mark {$expectedAbsentCount} teachers as absent"
            );

            // Verify all teachers without attendance now have absent records
            $teachersWithoutRecords = array_slice($teachers, $teachersWithAttendance);
            foreach ($teachersWithoutRecords as $teacher) {
                $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                    ->whereDate('attendance_date', Carbon::today())
                    ->first();

                $this->assertNotNull($attendance, "Absent record should exist for teacher {$teacher->id}");
                $this->assertEquals('absent', $attendance->attendance_status, "Status should be 'absent'");
                $this->assertNull($attendance->time_in, "time_in should be null for absent teachers");
                $this->assertNull($attendance->first_student_scan, "first_student_scan should be null");
                $this->assertEquals($schoolYear->id, $attendance->school_year_id, "Should be associated with active school year");
            }

            // Verify teachers with existing records are unchanged
            foreach ($teachersWithRecords as $teacher) {
                $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                    ->whereDate('attendance_date', Carbon::today())
                    ->first();

                $this->assertNotNull($attendance);
                $this->assertEquals('pending', $attendance->attendance_status, "Existing records should remain unchanged");
            }
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 40: Mark no_scan updates pending records**
     * **Validates: Requirements 12.2**
     * 
     * For any teacher_attendance record with attendance_status='pending' when markNoScanTeachers() runs,
     * the attendance_status SHALL be updated to 'no_scan'.
     */
    public function testMarkNoScanUpdatesPendingRecords(): void
    {
        $this->forAll(
            Generator\choose(1, 10),  // pending records
            Generator\choose(0, 5),   // confirmed records
            Generator\choose(0, 5)    // late records
        )
        ->withMaxSize(100)
        ->then(function ($pendingCount, $confirmedCount, $lateCount) {
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

            $pendingTeachers = [];
            $confirmedTeachers = [];
            $lateTeachers = [];

            // Create pending attendance records
            for ($i = 0; $i < $pendingCount; $i++) {
                $teacher = User::create([
                    'username' => 'teacher_pending_' . $i . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Pending Teacher ' . $i,
                    'is_active' => true,
                ]);
                $pendingTeachers[] = $teacher;

                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'school_year_id' => $schoolYear->id,
                    'attendance_date' => Carbon::today(),
                    'time_in' => Carbon::now(),
                    'attendance_status' => 'pending',
                    'late_status' => null,
                ]);
            }

            // Create confirmed attendance records
            for ($i = 0; $i < $confirmedCount; $i++) {
                $teacher = User::create([
                    'username' => 'teacher_confirmed_' . $i . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Confirmed Teacher ' . $i,
                    'is_active' => true,
                ]);
                $confirmedTeachers[] = $teacher;

                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'school_year_id' => $schoolYear->id,
                    'attendance_date' => Carbon::today(),
                    'time_in' => Carbon::now(),
                    'first_student_scan' => Carbon::now(),
                    'attendance_status' => 'confirmed',
                    'late_status' => 'on_time',
                ]);
            }

            // Create late attendance records
            for ($i = 0; $i < $lateCount; $i++) {
                $teacher = User::create([
                    'username' => 'teacher_late_' . $i . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Late Teacher ' . $i,
                    'is_active' => true,
                ]);
                $lateTeachers[] = $teacher;

                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'school_year_id' => $schoolYear->id,
                    'attendance_date' => Carbon::today(),
                    'time_in' => Carbon::now(),
                    'first_student_scan' => Carbon::now(),
                    'attendance_status' => 'late',
                    'late_status' => 'late',
                ]);
            }

            // Run markNoScanTeachers
            $markedCount = $this->service->markNoScanTeachers();

            // Verify the count
            $this->assertEquals(
                $pendingCount,
                $markedCount,
                "Should mark {$pendingCount} teachers as no_scan"
            );

            // Verify all pending records are now no_scan
            foreach ($pendingTeachers as $teacher) {
                $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                    ->whereDate('attendance_date', Carbon::today())
                    ->first();

                $this->assertNotNull($attendance);
                $this->assertEquals('no_scan', $attendance->attendance_status, "Pending records should be updated to 'no_scan'");
            }

            // Verify confirmed records are unchanged
            foreach ($confirmedTeachers as $teacher) {
                $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                    ->whereDate('attendance_date', Carbon::today())
                    ->first();

                $this->assertNotNull($attendance);
                $this->assertEquals('confirmed', $attendance->attendance_status, "Confirmed records should remain unchanged");
            }

            // Verify late records are unchanged
            foreach ($lateTeachers as $teacher) {
                $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                    ->whereDate('attendance_date', Carbon::today())
                    ->first();

                $this->assertNotNull($attendance);
                $this->assertEquals('late', $attendance->attendance_status, "Late records should remain unchanged");
            }
        });
    }
}
