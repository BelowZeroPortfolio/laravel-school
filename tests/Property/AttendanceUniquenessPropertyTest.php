<?php

namespace Tests\Property;

use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * **Feature: qr-attendance-laravel-migration, Property 46: Attendance uniqueness per day**
 * **Validates: Requirements 15.4**
 */
class AttendanceUniquenessPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 46: Attendance uniqueness per day**
     * 
     * For any (student_id, attendance_date) combination, at most one attendance record SHALL exist.
     * For any (teacher_id, attendance_date) combination, at most one teacher_attendance record SHALL exist.
     */
    public function testStudentAttendanceUniquenessConstraint(): void
    {
        $this->forAll(
            Generator\choose(1, 100),  // student_id
            Generator\choose(1, 28),   // day of month
            Generator\choose(1, 12)    // month
        )
        ->withMaxSize(100)
        ->then(function ($studentId, $day, $month) {
            // Create a school year first
            $schoolYearId = DB::table('school_years')->insertGetId([
                'name' => 'SY ' . uniqid(),
                'is_active' => true,
                'is_locked' => false,
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create a student
            $actualStudentId = DB::table('students')->insertGetId([
                'student_id' => 'STU-' . uniqid(),
                'lrn' => str_pad($studentId, 12, '0', STR_PAD_LEFT) . uniqid(),
                'first_name' => 'Test',
                'last_name' => 'Student',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $attendanceDate = sprintf('2025-%02d-%02d', $month, $day);

            // Insert first attendance record
            DB::table('attendance')->insert([
                'student_id' => $actualStudentId,
                'school_year_id' => $schoolYearId,
                'attendance_date' => $attendanceDate,
                'check_in_time' => now(),
                'status' => 'present',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Attempt to insert duplicate - should fail due to unique constraint
            $exceptionThrown = false;
            try {
                DB::table('attendance')->insert([
                    'student_id' => $actualStudentId,
                    'school_year_id' => $schoolYearId,
                    'attendance_date' => $attendanceDate,
                    'check_in_time' => now(),
                    'status' => 'late',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue(
                $exceptionThrown,
                "Duplicate attendance record should be prevented by unique constraint"
            );

            // Verify only one record exists
            $count = DB::table('attendance')
                ->where('student_id', $actualStudentId)
                ->where('attendance_date', $attendanceDate)
                ->count();

            $this->assertEquals(1, $count, "Only one attendance record should exist per student per day");
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 46: Attendance uniqueness per day**
     * 
     * For any (teacher_id, attendance_date) combination, at most one teacher_attendance record SHALL exist.
     */
    public function testTeacherAttendanceUniquenessConstraint(): void
    {
        $this->forAll(
            Generator\choose(1, 100),  // teacher_id
            Generator\choose(1, 28),   // day of month
            Generator\choose(1, 12)    // month
        )
        ->withMaxSize(100)
        ->then(function ($teacherId, $day, $month) {
            // Create a school year first
            $schoolYearId = DB::table('school_years')->insertGetId([
                'name' => 'SY ' . uniqid(),
                'is_active' => true,
                'is_locked' => false,
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create a teacher user
            $actualTeacherId = DB::table('users')->insertGetId([
                'username' => 'teacher_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Test Teacher',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $attendanceDate = sprintf('2025-%02d-%02d', $month, $day);

            // Insert first teacher attendance record
            DB::table('teacher_attendance')->insert([
                'teacher_id' => $actualTeacherId,
                'school_year_id' => $schoolYearId,
                'attendance_date' => $attendanceDate,
                'time_in' => now(),
                'attendance_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Attempt to insert duplicate - should fail due to unique constraint
            $exceptionThrown = false;
            try {
                DB::table('teacher_attendance')->insert([
                    'teacher_id' => $actualTeacherId,
                    'school_year_id' => $schoolYearId,
                    'attendance_date' => $attendanceDate,
                    'time_in' => now(),
                    'attendance_status' => 'confirmed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue(
                $exceptionThrown,
                "Duplicate teacher attendance record should be prevented by unique constraint"
            );

            // Verify only one record exists
            $count = DB::table('teacher_attendance')
                ->where('teacher_id', $actualTeacherId)
                ->where('attendance_date', $attendanceDate)
                ->count();

            $this->assertEquals(1, $count, "Only one teacher attendance record should exist per teacher per day");
        });
    }
}
