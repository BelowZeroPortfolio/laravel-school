<?php

namespace App\Policies;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClassRoomPolicy
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
     * Determine whether the user can view any classes.
     * Teachers can only view their own classes.
     * Principals can view all classes.
     */
    public function viewAny(User $user): bool
    {
        return $user->isPrincipal() || $user->isTeacher();
    }

    /**
     * Determine whether the user can view the class.
     * Teachers can only view classes assigned to them.
     */
    public function view(User $user, ClassRoom $classRoom): bool
    {
        if ($user->isPrincipal()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $classRoom->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create classes.
     * Only admins can create classes (handled by before()).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the class.
     * Only admins can update classes (handled by before()).
     */
    public function update(User $user, ClassRoom $classRoom): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the class.
     * Only admins can delete classes (handled by before()).
     */
    public function delete(User $user, ClassRoom $classRoom): bool
    {
        return false;
    }
}
