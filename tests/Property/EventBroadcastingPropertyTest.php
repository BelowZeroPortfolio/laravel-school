<?php

namespace Tests\Property;

use App\Events\AttendanceFinalized;
use App\Events\StudentScanned;
use App\Events\TeacherLoggedIn;
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
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Property-based tests for Event Broadcasting
 */
class EventBroadcastingPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected StudentAttendanceService $studentService;
    protected TeacherAttendanceService $teacherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
        $this->teacherService = new TeacherAttendanceService();
        $this->studentService = new StudentAttendanceService($this->teacherService);
    }

    /**
     * Helper method to clean up database state between iterations.
     */
    protected function cleanupDatabase(): void
    {
        Attendance::query()->delete();
        TeacherAttendance::query()->delete();
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
     * **Feature: qr-attendance-laravel-migration, Property 42: Student scan broadcasts event**
     * **Validates: Requirements 13.1**
     * 
     * For any successful student attendance recording, a StudentScanned event SHALL be
     * dispatched to the channel 'attendance.{school_year_id}'.
     */
    public function testStudentScanBroadcastsEvent(): void
    {
        $this->forAll(
            Generator\choose(1, 100)  // student index for uniqueness
        )
        ->withMaxSize(100)
        ->then(function ($studentIndex) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Fake events to capture dispatched events
            Event::fake([StudentScanned::class]);

            // Create base test data
            $baseData = $this->createBaseTestData();
            $schoolYear = $baseData['schoolYear'];

            // Create a student with unique LRN
            $lrn = str_pad($studentIndex, 12, '0', STR_PAD_LEFT);
            $student = Student::create([
                'student_id' => 'STU-' . uniqid(),
                'lrn' => $lrn,
                'first_name' => 'Test',
                'last_name' => 'Student ' . $studentIndex,
                'is_active' => true,
            ]);

            // Process QR code scan
            $result = $this->studentService->processQRCodeScan($lrn);

            // Verify scan succeeded
            $this->assertTrue($result['success'], "Scan should succeed for valid QR code");

            // Verify StudentScanned event was dispatched
            Event::assertDispatched(StudentScanned::class, function ($event) use ($student, $schoolYear) {
                // Verify event contains correct student
                $this->assertEquals($student->id, $event->student->id);
                
                // Verify event contains attendance with correct school_year_id
                $this->assertEquals($schoolYear->id, $event->attendance->school_year_id);
                
                // Verify broadcast channel is correct
                $channels = $event->broadcastOn();
                $this->assertCount(1, $channels);
                $this->assertEquals('attendance.' . $schoolYear->id, $channels[0]->name);
                
                return true;
            });
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 43: Teacher login broadcasts event**
     * **Validates: Requirements 13.2**
     * 
     * For any successful teacher login, a TeacherLoggedIn event SHALL be dispatched
     * to the channel 'teacher-monitoring.{school_year_id}'.
     */
    public function testTeacherLoginBroadcastsEvent(): void
    {
        $this->forAll(
            Generator\choose(1, 100)  // teacher index for uniqueness
        )
        ->withMaxSize(100)
        ->then(function ($teacherIndex) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Fake events to capture dispatched events
            Event::fake([TeacherLoggedIn::class]);

            // Create base test data
            $baseData = $this->createBaseTestData();
            $schoolYear = $baseData['schoolYear'];

            // Create a teacher
            $teacher = User::create([
                'username' => 'teacher_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'full_name' => 'Test Teacher ' . $teacherIndex,
                'is_active' => true,
            ]);

            // Record teacher time_in (simulates login)
            $result = $this->teacherService->recordTimeIn($teacher->id);

            // Verify login succeeded
            $this->assertTrue($result, "Teacher login should succeed");

            // Verify TeacherLoggedIn event was dispatched
            Event::assertDispatched(TeacherLoggedIn::class, function ($event) use ($teacher, $schoolYear) {
                // Verify event contains correct teacher
                $this->assertEquals($teacher->id, $event->teacher->id);
                
                // Verify event contains attendance with correct school_year_id
                $this->assertEquals($schoolYear->id, $event->attendance->school_year_id);
                
                // Verify attendance status is pending
                $this->assertEquals('pending', $event->attendance->attendance_status);
                
                // Verify broadcast channel is correct
                $channels = $event->broadcastOn();
                $this->assertCount(1, $channels);
                $this->assertEquals('teacher-monitoring.' . $schoolYear->id, $channels[0]->name);
                
                return true;
            });
        });
    }
}
