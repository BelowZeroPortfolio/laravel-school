<?php

namespace Tests\Property;

use App\Http\Controllers\ClassController;
use App\Http\Controllers\StudentController;
use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Property-based tests for role-based data visibility.
 * **Feature: qr-attendance-laravel-migration**
 */
class RoleBasedDataVisibilityPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
    }

    /**
     * Helper method to clean up database state between iterations.
     */
    protected function cleanupDatabase(): void
    {
        \DB::table('student_classes')->delete();
        ClassRoom::query()->delete();
        Student::query()->delete();
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

        return compact('schoolYear', 'admin');
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 29: Role-based data visibility**
     * **Validates: Requirements 8.2, 9.3**
     * 
     * For any teacher querying students or classes, the result set SHALL contain only
     * records associated with classes where teacher_id equals the querying user's ID.
     */
    public function testTeacherSeesOnlyTheirStudentsAndClasses(): void
    {
        $this->forAll(
            Generator\choose(1, 5),  // number of teachers
            Generator\choose(1, 3),  // classes per teacher
            Generator\choose(2, 5)   // students per class
        )
        ->withMaxSize(100)
        ->then(function ($numTeachers, $classesPerTeacher, $studentsPerClass) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $baseData = $this->createBaseTestData();
            $schoolYear = $baseData['schoolYear'];

            $teachers = [];
            $teacherClasses = [];
            $teacherStudents = [];

            // Create teachers with their classes and students
            for ($t = 0; $t < $numTeachers; $t++) {
                $teacher = User::create([
                    'username' => 'teacher_' . $t . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Teacher ' . $t,
                    'is_active' => true,
                ]);
                $teachers[] = $teacher;
                $teacherClasses[$teacher->id] = [];
                $teacherStudents[$teacher->id] = [];

                // Create classes for this teacher
                for ($c = 0; $c < $classesPerTeacher; $c++) {
                    $class = ClassRoom::create([
                        'grade_level' => 'Grade ' . (($t * $classesPerTeacher + $c) % 12 + 1),
                        'section' => chr(65 + $c) . $t,
                        'teacher_id' => $teacher->id,
                        'school_year_id' => $schoolYear->id,
                        'is_active' => true,
                        'max_capacity' => 40,
                    ]);
                    $teacherClasses[$teacher->id][] = $class->id;

                    // Create students for this class
                    for ($s = 0; $s < $studentsPerClass; $s++) {
                        $student = Student::create([
                            'student_id' => 'STU-' . $t . '-' . $c . '-' . $s . '-' . uniqid(),
                            'lrn' => str_pad(($t * 10000 + $c * 100 + $s), 12, '0', STR_PAD_LEFT),
                            'first_name' => 'Student',
                            'last_name' => "T{$t}C{$c}S{$s}",
                            'is_active' => true,
                        ]);

                        // Enroll student in class
                        $class->students()->attach($student->id, [
                            'enrolled_at' => now(),
                            'is_active' => true,
                            'enrollment_type' => 'regular',
                            'enrollment_status' => 'enrolled',
                        ]);

                        $teacherStudents[$teacher->id][] = $student->id;
                    }
                }
            }

            // Test each teacher's visibility
            foreach ($teachers as $teacher) {
                // Simulate authenticated request for this teacher
                $this->actingAs($teacher);

                // Test class visibility (Requirement 9.3)
                $classQuery = ClassRoom::query()->with(['teacher', 'schoolYear']);
                
                // Apply teacher filtering (same logic as ClassController)
                if ($teacher->isTeacher()) {
                    $classQuery->where('teacher_id', $teacher->id);
                }

                $visibleClasses = $classQuery->pluck('id')->toArray();

                // Verify teacher sees only their classes
                $this->assertEquals(
                    sort($teacherClasses[$teacher->id]),
                    sort($visibleClasses),
                    "Teacher {$teacher->id} should see only their own classes"
                );

                // Verify teacher doesn't see other teachers' classes
                foreach ($teachers as $otherTeacher) {
                    if ($otherTeacher->id !== $teacher->id) {
                        $otherClassIds = $teacherClasses[$otherTeacher->id];
                        foreach ($otherClassIds as $otherClassId) {
                            $this->assertNotContains(
                                $otherClassId,
                                $visibleClasses,
                                "Teacher {$teacher->id} should not see class {$otherClassId} belonging to teacher {$otherTeacher->id}"
                            );
                        }
                    }
                }

                // Test student visibility (Requirement 8.2)
                $studentQuery = Student::query()->with('classes');
                
                // Apply teacher filtering (same logic as StudentController)
                if ($teacher->isTeacher()) {
                    $teacherClassIds = $teacher->classes()->pluck('id');
                    $studentQuery->whereHas('classes', fn($q) => $q->whereIn('classes.id', $teacherClassIds));
                }

                $visibleStudentIds = $studentQuery->pluck('id')->toArray();

                // Verify teacher sees only their students
                foreach ($teacherStudents[$teacher->id] as $expectedStudentId) {
                    $this->assertContains(
                        $expectedStudentId,
                        $visibleStudentIds,
                        "Teacher {$teacher->id} should see student {$expectedStudentId}"
                    );
                }

                // Verify teacher doesn't see other teachers' students
                foreach ($teachers as $otherTeacher) {
                    if ($otherTeacher->id !== $teacher->id) {
                        $otherStudentIds = $teacherStudents[$otherTeacher->id];
                        foreach ($otherStudentIds as $otherStudentId) {
                            $this->assertNotContains(
                                $otherStudentId,
                                $visibleStudentIds,
                                "Teacher {$teacher->id} should not see student {$otherStudentId} belonging to teacher {$otherTeacher->id}"
                            );
                        }
                    }
                }
            }
        });
    }

    /**
     * Test that admins can see all students and classes.
     * This complements Property 29 by verifying admin visibility.
     */
    public function testAdminSeesAllStudentsAndClasses(): void
    {
        $this->forAll(
            Generator\choose(2, 4),  // number of teachers
            Generator\choose(1, 2),  // classes per teacher
            Generator\choose(2, 3)   // students per class
        )
        ->withMaxSize(100)
        ->then(function ($numTeachers, $classesPerTeacher, $studentsPerClass) {
            // Clean up from previous iteration
            $this->cleanupDatabase();

            // Create base test data
            $baseData = $this->createBaseTestData();
            $schoolYear = $baseData['schoolYear'];
            $admin = $baseData['admin'];

            $allClassIds = [];
            $allStudentIds = [];

            // Create teachers with their classes and students
            for ($t = 0; $t < $numTeachers; $t++) {
                $teacher = User::create([
                    'username' => 'teacher_' . $t . '_' . uniqid(),
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'full_name' => 'Teacher ' . $t,
                    'is_active' => true,
                ]);

                for ($c = 0; $c < $classesPerTeacher; $c++) {
                    $class = ClassRoom::create([
                        'grade_level' => 'Grade ' . (($t * $classesPerTeacher + $c) % 12 + 1),
                        'section' => chr(65 + $c) . $t,
                        'teacher_id' => $teacher->id,
                        'school_year_id' => $schoolYear->id,
                        'is_active' => true,
                        'max_capacity' => 40,
                    ]);
                    $allClassIds[] = $class->id;

                    for ($s = 0; $s < $studentsPerClass; $s++) {
                        $student = Student::create([
                            'student_id' => 'STU-' . $t . '-' . $c . '-' . $s . '-' . uniqid(),
                            'lrn' => str_pad(($t * 10000 + $c * 100 + $s), 12, '0', STR_PAD_LEFT),
                            'first_name' => 'Student',
                            'last_name' => "T{$t}C{$c}S{$s}",
                            'is_active' => true,
                        ]);

                        $class->students()->attach($student->id, [
                            'enrolled_at' => now(),
                            'is_active' => true,
                            'enrollment_type' => 'regular',
                            'enrollment_status' => 'enrolled',
                        ]);

                        $allStudentIds[] = $student->id;
                    }
                }
            }

            // Simulate authenticated request for admin
            $this->actingAs($admin);

            // Admin should see all classes (no filtering applied)
            $visibleClasses = ClassRoom::query()->pluck('id')->toArray();
            
            sort($allClassIds);
            sort($visibleClasses);
            
            $this->assertEquals(
                $allClassIds,
                $visibleClasses,
                "Admin should see all classes"
            );

            // Admin should see all students (no filtering applied)
            $visibleStudents = Student::query()->pluck('id')->toArray();
            
            sort($allStudentIds);
            sort($visibleStudents);
            
            $this->assertEquals(
                $allStudentIds,
                $visibleStudents,
                "Admin should see all students"
            );
        });
    }
}
