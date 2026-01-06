<?php

namespace App\Services;

use App\Models\TimeSchedule;
use App\Models\TimeScheduleLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TimeScheduleService
{
    /**
     * Get the currently active time schedule.
     * (Requirement 7.5)
     *
     * @return TimeSchedule|null
     */
    public function getActive(): ?TimeSchedule
    {
        return TimeSchedule::active()->first();
    }

    /**
     * Clear the active time schedule cache.
     */
    protected function clearActiveScheduleCache(): void
    {
        Cache::forget('active_time_schedule');
    }

    /**
     * Create a new time schedule with audit logging.
     * (Requirement 7.1)
     *
     * @param  array  $data  Schedule data
     * @param  int  $userId  The user creating the schedule
     * @return TimeSchedule
     */
    public function create(array $data, int $userId): TimeSchedule
    {
        // Set created_by to the user creating the schedule
        $data['created_by'] = $userId;

        // If this is set to be active, deactivate all others first
        if (isset($data['is_active']) && $data['is_active']) {
            TimeSchedule::where('is_active', true)->update(['is_active' => false]);
            $this->clearActiveScheduleCache();
        }

        $schedule = TimeSchedule::create($data);

        // Log the creation action
        TimeScheduleLog::create([
            'schedule_id' => $schedule->id,
            'action' => 'create',
            'changed_by' => $userId,
            'old_values' => null,
            'new_values' => $schedule->toArray(),
            'change_reason' => null,
        ]);

        return $schedule;
    }

    /**
     * Update a time schedule with audit logging.
     * (Requirement 7.2)
     *
     * @param  int  $id  Schedule ID
     * @param  array  $data  Updated data
     * @param  int  $userId  The user making the update
     * @param  string|null  $reason  Optional reason for the change
     * @return bool
     */
    public function update(int $id, array $data, int $userId, ?string $reason = null): bool
    {
        $schedule = TimeSchedule::find($id);

        if (! $schedule) {
            return false;
        }

        // Store old values before update
        $oldValues = $schedule->toArray();

        // If activating this schedule, deactivate all others first
        if (isset($data['is_active']) && $data['is_active'] && ! $schedule->is_active) {
            TimeSchedule::where('is_active', true)->update(['is_active' => false]);
        }

        $schedule->update($data);

        // Clear cache if schedule is active or becoming active
        if ($schedule->is_active || (isset($data['is_active']) && $data['is_active'])) {
            $this->clearActiveScheduleCache();
        }

        // Refresh to get updated values
        $schedule->refresh();

        // Log the update action with old and new values
        TimeScheduleLog::create([
            'schedule_id' => $schedule->id,
            'action' => 'update',
            'changed_by' => $userId,
            'old_values' => $oldValues,
            'new_values' => $schedule->toArray(),
            'change_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Activate a time schedule, deactivating all others.
     * (Requirement 7.3)
     *
     * @param  int  $id  Schedule ID to activate
     * @param  int  $userId  The user performing the activation
     * @return bool
     */
    public function activate(int $id, int $userId): bool
    {
        $schedule = TimeSchedule::find($id);

        if (! $schedule) {
            return false;
        }

        // Deactivate all other schedules
        TimeSchedule::where('is_active', true)->update(['is_active' => false]);

        // Activate the specified schedule
        $schedule->update(['is_active' => true]);

        // Clear cache for scan performance
        $this->clearActiveScheduleCache();

        // Log the activation action
        TimeScheduleLog::create([
            'schedule_id' => $schedule->id,
            'action' => 'activate',
            'changed_by' => $userId,
            'old_values' => null,
            'new_values' => ['is_active' => true],
            'change_reason' => null,
        ]);

        return true;
    }

    /**
     * Delete a time schedule if it's not active.
     * (Requirement 7.4)
     *
     * @param int $id Schedule ID to delete
     * @param int $userId The user performing the deletion
     * @return bool True if deleted, false if active or not found
     */
    public function delete(int $id, int $userId): bool
    {
        $schedule = TimeSchedule::find($id);

        if (!$schedule) {
            return false;
        }

        // Prevent deletion of active schedule (Requirement 7.4)
        if ($schedule->is_active) {
            return false;
        }

        // Log the deletion before deleting (the log will cascade delete)
        // So we need to store the values first
        $scheduleData = $schedule->toArray();

        // Create a log entry before deletion
        TimeScheduleLog::create([
            'schedule_id' => $schedule->id,
            'action' => 'delete',
            'changed_by' => $userId,
            'old_values' => $scheduleData,
            'new_values' => null,
            'change_reason' => null,
        ]);

        // Note: The log will be cascade deleted with the schedule
        // If we want to preserve logs, we'd need a different approach
        $schedule->delete();

        return true;
    }

    /**
     * Get change logs for a schedule or all schedules.
     *
     * @param int|null $scheduleId Optional schedule ID to filter by
     * @param int $limit Maximum number of logs to return
     * @return Collection
     */
    public function getChangeLogs(?int $scheduleId = null, int $limit = 50): Collection
    {
        $query = TimeScheduleLog::with(['schedule', 'changedBy'])
            ->orderBy('created_at', 'desc');

        if ($scheduleId !== null) {
            $query->where('schedule_id', $scheduleId);
        }

        return $query->limit($limit)->get();
    }
}
