<?php

namespace App\Policies;

use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolYearPolicy
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
     * Determine whether the user can view any school years.
     * Only admins can manage school years.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the school year.
     * Only admins can view school years.
     */
    public function view(User $user, SchoolYear $schoolYear): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create school years.
     * Only admins can create school years.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the school year.
     * Only admins can update school years.
     */
    public function update(User $user, SchoolYear $schoolYear): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the school year.
     * Only admins can delete school years.
     */
    public function delete(User $user, SchoolYear $schoolYear): bool
    {
        return false;
    }
}
