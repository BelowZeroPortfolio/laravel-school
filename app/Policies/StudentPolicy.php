<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     * Admin bypasses all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any students.
     * Teachers can only view students in their classes.
     * Principals can view all students.
     */
    public function viewAny(User $user): bool
    {
        return $user->isPrincipal() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view the student.
     * Teachers can only view students enrolled in their classes.
     */
    public function view(User $user, Student $student): bool
    {
        if ($user->isPrincipal()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $this->studentBelongsToTeacher($user, $student);
        }

        return false;
    }

    /**
     * Determine whether the user can create students.
     * Teachers and principals can create students.
     */
    public function create(User $user): bool
    {
        return $user->isPrincipal() || $user->isTeacher();
    }

    /**
     * Determine whether the user can update the student.
     * Teachers can only update students in their classes.
     */
    public function update(User $user, Student $student): bool
    {
        if ($user->isPrincipal()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $this->studentBelongsToTeacher($user, $student);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the student.
     * Only admins can delete students (handled by before()).
     */
    public function delete(User $user, Student $student): bool
    {
        return false;
    }

    /**
     * Check if a student belongs to any of the teacher's classes.
     */
    private function studentBelongsToTeacher(User $teacher, Student $student): bool
    {
        $teacherClassIds = $teacher->classes()->pluck('id');
        
        return $student->classes()
            ->whereIn('classes.id', $teacherClassIds)
            ->exists();
    }
}
