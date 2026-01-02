<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentPlacementService
{
    /**
     * Transfer a student from one class to another.
     * Updates old enrollment to 'transferred_out', creates new enrollment.
     * (Requirements 19.1, 19.2)
     *
     * @param int $studentId The student's ID
     * @param int $fromClassId The source class ID
     * @param int $toClassId The target class ID
     * @param int $userId The user performing the transfer
     * @param string|null $reason Optional reason for the transfer
     * @return bool True if successful
     */
    public function transferStudent(
        int $studentId,
        int $fromClassId,
        int $toClassId,
        int $userId,
        ?string $reason = null
    ): bool {
        $student = Student::find($studentId);
        $fromClass = ClassRoom::find($fromClassId);
        $toClass = ClassRoom::find($toClassId);

        if (!$student || !$fromClass || !$toClass) {
            return false;
        }

        // Check if target class has capacity
        if ($toClass->isAtCapacity()) {
            return false;
        }

        // Check if student is enrolled in the source class
        $existingEnrollment = $student->classes()
            ->where('class_id', $fromClassId)
            ->wherePivot('is_active', true)
            ->first();

        if (!$existingEnrollment) {
            return false;
        }

        $now = Carbon::now();

        return DB::transaction(function () use ($student, $fromClassId, $toClassId, $userId, $reason, $now) {
            // Update old enrollment to 'transferred_out' (Requirement 19.1)
            $student->classes()->updateExistingPivot($fromClassId, [
                'is_active' => false,
                'enrollment_status' => 'transferred',
                'status_changed_at' => $now,
                'status_changed_by' => $userId,
                'status_reason' => $reason ?? 'Transferred to another class',
            ]);

            // Check if student already has an enrollment in target class
            $existingTargetEnrollment = $student->classes()
                ->where('class_id', $toClassId)
                ->first();

            if ($existingTargetEnrollment) {
                // Reactivate existing enrollment
                $student->classes()->updateExistingPivot($toClassId, [
                    'is_active' => true,
                    'enrollment_type' => 'transferee',
                    'enrollment_status' => 'enrolled',
                    'status_changed_at' => $now,
                    'status_changed_by' => $userId,
                    'status_reason' => $reason ?? 'Transferred from another class',
                ]);
            } else {
                // Create new enrollment in target class (Requirement 19.2)
                $student->classes()->attach($toClassId, [
                    'enrolled_at' => $now,
                    'enrolled_by' => $userId,
                    'is_active' => true,
                    'enrollment_type' => 'transferee',
                    'enrollment_status' => 'enrolled',
                    'status_changed_at' => $now,
                    'status_changed_by' => $userId,
                    'status_reason' => $reason ?? 'Transferred from another class',
                ]);
            }

            return true;
        });
    }


    /**
     * Place a student in a class.
     * Creates new student_classes record with enrollment metadata.
     * (Requirements 19.2, 19.4)
     *
     * @param int $studentId The student's ID
     * @param int $classId The target class ID
     * @param string $enrollmentType The enrollment type ('regular', 'transferee', 'returnee')
     * @param int $userId The user performing the placement
     * @param string|null $reason Optional reason for the placement
     * @return bool True if successful
     */
    public function placeStudent(
        int $studentId,
        int $classId,
        string $enrollmentType,
        int $userId,
        ?string $reason = null
    ): bool {
        $student = Student::find($studentId);
        $class = ClassRoom::find($classId);

        if (!$student || !$class) {
            return false;
        }

        // Check if class has capacity (Requirement 8.5)
        if ($class->isAtCapacity()) {
            return false;
        }

        // Check if student is already enrolled in this class
        $existingEnrollment = $student->classes()
            ->where('class_id', $classId)
            ->first();

        $now = Carbon::now();

        if ($existingEnrollment) {
            // Reactivate existing enrollment if inactive
            if (!$existingEnrollment->pivot->is_active) {
                $student->classes()->updateExistingPivot($classId, [
                    'is_active' => true,
                    'enrollment_type' => $enrollmentType,
                    'enrollment_status' => 'enrolled',
                    'status_changed_at' => $now,
                    'status_changed_by' => $userId,
                    'status_reason' => $reason ?? 'Re-enrolled in class',
                ]);
                return true;
            }
            // Already actively enrolled
            return false;
        }

        // Create new enrollment (Requirements 19.2, 19.4)
        $student->classes()->attach($classId, [
            'enrolled_at' => $now,
            'enrolled_by' => $userId,
            'is_active' => true,
            'enrollment_type' => $enrollmentType,
            'enrollment_status' => 'enrolled',
            'status_changed_at' => $now,
            'status_changed_by' => $userId,
            'status_reason' => $reason ?? 'Initial enrollment',
        ]);

        return true;
    }

    /**
     * Place multiple students in a target class.
     * (Requirement 19.3)
     *
     * @param array $studentIds Array of student IDs
     * @param int $classId The target class ID
     * @param string $enrollmentType The enrollment type
     * @param int $userId The user performing the placement
     * @param string|null $reason Optional reason for the placement
     * @return int Number of students successfully placed
     */
    public function bulkPlaceStudents(
        array $studentIds,
        int $classId,
        string $enrollmentType,
        int $userId,
        ?string $reason = null
    ): int {
        $class = ClassRoom::find($classId);

        if (!$class) {
            return 0;
        }

        $successCount = 0;

        foreach ($studentIds as $studentId) {
            // Check capacity before each placement
            if ($class->isAtCapacity()) {
                break;
            }

            if ($this->placeStudent($studentId, $classId, $enrollmentType, $userId, $reason)) {
                $successCount++;
                // Refresh the class to get updated student count
                $class->refresh();
            }
        }

        return $successCount;
    }

    /**
     * Get all enrollments for a student across school years.
     * (Requirement 19.5)
     *
     * @param int $studentId The student's ID
     * @return Collection Collection of enrollment records with class and school year info
     */
    public function getPlacementHistory(int $studentId): Collection
    {
        $student = Student::with(['classes' => function ($query) {
            $query->with('schoolYear')
                ->orderBy('student_classes.enrolled_at', 'desc');
        }])->find($studentId);

        if (!$student) {
            return collect();
        }

        return $student->classes->map(function ($class) {
            return [
                'class_id' => $class->id,
                'grade_level' => $class->grade_level,
                'section' => $class->section,
                'school_year' => $class->schoolYear?->name,
                'school_year_id' => $class->school_year_id,
                'enrolled_at' => $class->pivot->enrolled_at,
                'enrolled_by' => $class->pivot->enrolled_by,
                'is_active' => $class->pivot->is_active,
                'enrollment_type' => $class->pivot->enrollment_type,
                'enrollment_status' => $class->pivot->enrollment_status,
                'status_changed_at' => $class->pivot->status_changed_at,
                'status_changed_by' => $class->pivot->status_changed_by,
                'status_reason' => $class->pivot->status_reason,
            ];
        });
    }
}
