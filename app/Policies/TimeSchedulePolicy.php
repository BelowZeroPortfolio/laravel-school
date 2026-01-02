<?php

namespace App\Policies;

use App\Models\TimeSchedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimeSchedulePolicy
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
     * Determine whether the user can view any time schedules.
     * Only admins can view time schedules.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the time schedule.
     * Only admins can view time schedules.
     */
    public function view(User $user, TimeSchedule $timeSchedule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create time schedules.
     * Only admins can create time schedules.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the time schedule.
     * Only admins can update time schedules.
     */
    public function update(User $user, TimeSchedule $timeSchedule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the time schedule.
     * Only admins can delete time schedules.
     */
    public function delete(User $user, TimeSchedule $timeSchedule): bool
    {
        return false;
    }
}
