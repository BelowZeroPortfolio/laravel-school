<?php

namespace Tests\Property;

use App\Models\TimeSchedule;
use App\Models\TimeScheduleLog;
use App\Models\User;
use App\Services\TimeScheduleService;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property tests for TimeScheduleService
 * Tests Properties 24, 25, and 27 from the design document.
 */
class TimeScheduleServicePropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected TimeScheduleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
        $this->service = new TimeScheduleService();
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 24: Time schedule creation logged**
     * **Validates: Requirements 7.1**
     * 
     * For any TimeSchedule creation, a time_schedule_logs entry SHALL exist with 
     * action='create' and changed_by set to the creating user's ID.
     */
    public function testTimeScheduleCreationLogged(): void
    {
        $this->forAll(
            Generator\choose(6, 10),   // time_in hour
            Generator\choose(14, 18),  // time_out hour
            Generator\choose(5, 60)    // late_threshold_minutes
        )
        ->withMaxSize(100)
        ->then(function ($timeInHour, $timeOutHour, $threshold) {
            // Create an admin user
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin User',
                'is_active' => true,
            ]);

            $scheduleData = [
                'name' => 'Test Schedule ' . uniqid(),
                'time_in' => sprintf('%02d:00:00', $timeInHour),
                'time_out' => sprintf('%02d:00:00', $timeOutHour),
                'late_threshold_minutes' => $threshold,
                'is_active' => false,
                'effective_date' => now()->toDateString(),
            ];

            // Create schedule using the service
            $schedule = $this->service->create($scheduleData, $admin->id);

            // Verify a log entry exists with action='create'
            $log = TimeScheduleLog::where('schedule_id', $schedule->id)
                ->where('action', 'create')
                ->first();

            $this->assertNotNull(
                $log,
                "A time_schedule_logs entry with action='create' should exist"
            );

            // Verify changed_by is set to the creating user's ID
            $this->assertEquals(
                $admin->id,
                $log->changed_by,
                "The log's changed_by should be the creating user's ID"
            );

            // Verify new_values contains the schedule data
            $this->assertNotNull(
                $log->new_values,
                "The log's new_values should not be null"
            );

            // Verify old_values is null for creation
            $this->assertNull(
                $log->old_values,
                "The log's old_values should be null for creation"
            );
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 25: Time schedule update logged with values**
     * **Validates: Requirements 7.2**
     * 
     * For any TimeSchedule update, a time_schedule_logs entry SHALL exist with 
     * action='update', old_values containing previous field values, and 
     * new_values containing updated field values.
     */
    public function testTimeScheduleUpdateLoggedWithValues(): void
    {
        $this->forAll(
            Generator\choose(5, 30),   // original threshold
            Generator\choose(31, 60)   // new threshold (different from original)
        )
        ->withMaxSize(100)
        ->then(function ($originalThreshold, $newThreshold) {
            // Create an admin user
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin User',
                'is_active' => true,
            ]);

            // Create a schedule first
            $schedule = $this->service->create([
                'name' => 'Original Schedule ' . uniqid(),
                'time_in' => '07:00:00',
                'time_out' => '15:00:00',
                'late_threshold_minutes' => $originalThreshold,
                'is_active' => false,
                'effective_date' => now()->toDateString(),
            ], $admin->id);

            // Update the schedule
            $updateData = [
                'late_threshold_minutes' => $newThreshold,
            ];
            $reason = 'Testing update logging';

            $result = $this->service->update($schedule->id, $updateData, $admin->id, $reason);

            $this->assertTrue($result, "Update should succeed");

            // Verify a log entry exists with action='update'
            $log = TimeScheduleLog::where('schedule_id', $schedule->id)
                ->where('action', 'update')
                ->first();

            $this->assertNotNull(
                $log,
                "A time_schedule_logs entry with action='update' should exist"
            );

            // Verify changed_by is set correctly
            $this->assertEquals(
                $admin->id,
                $log->changed_by,
                "The log's changed_by should be the updating user's ID"
            );

            // Verify old_values contains the original threshold
            $this->assertNotNull(
                $log->old_values,
                "The log's old_values should not be null for updates"
            );
            $this->assertEquals(
                $originalThreshold,
                $log->old_values['late_threshold_minutes'],
                "old_values should contain the original late_threshold_minutes"
            );

            // Verify new_values contains the updated threshold
            $this->assertNotNull(
                $log->new_values,
                "The log's new_values should not be null for updates"
            );
            $this->assertEquals(
                $newThreshold,
                $log->new_values['late_threshold_minutes'],
                "new_values should contain the updated late_threshold_minutes"
            );

            // Verify change_reason is recorded
            $this->assertEquals(
                $reason,
                $log->change_reason,
                "The change_reason should be recorded"
            );
        });
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 27: Active schedule deletion prevented**
     * **Validates: Requirements 7.4**
     * 
     * For any TimeSchedule with is_active = true, a delete operation SHALL fail 
     * and the record SHALL remain in the database.
     */
    public function testActiveScheduleDeletionPrevented(): void
    {
        $this->forAll(
            Generator\choose(6, 10),   // time_in hour
            Generator\choose(14, 18),  // time_out hour
            Generator\choose(5, 60)    // late_threshold_minutes
        )
        ->withMaxSize(100)
        ->then(function ($timeInHour, $timeOutHour, $threshold) {
            // Create an admin user
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin User',
                'is_active' => true,
            ]);

            // Create an active schedule
            $schedule = $this->service->create([
                'name' => 'Active Schedule ' . uniqid(),
                'time_in' => sprintf('%02d:00:00', $timeInHour),
                'time_out' => sprintf('%02d:00:00', $timeOutHour),
                'late_threshold_minutes' => $threshold,
                'is_active' => true,  // This schedule is active
                'effective_date' => now()->toDateString(),
            ], $admin->id);

            $scheduleId = $schedule->id;

            // Attempt to delete the active schedule
            $result = $this->service->delete($scheduleId, $admin->id);

            // Verify deletion failed
            $this->assertFalse(
                $result,
                "Delete operation should fail for active schedule"
            );

            // Verify the record still exists in the database
            $existingSchedule = TimeSchedule::find($scheduleId);
            $this->assertNotNull(
                $existingSchedule,
                "The active schedule should still exist in the database"
            );

            // Verify it's still active
            $this->assertTrue(
                $existingSchedule->is_active,
                "The schedule should still be active"
            );
        });
    }

    /**
     * Test that inactive schedules can be deleted successfully.
     */
    public function testInactiveScheduleCanBeDeleted(): void
    {
        $this->forAll(
            Generator\choose(6, 10),   // time_in hour
            Generator\choose(14, 18),  // time_out hour
            Generator\choose(5, 60)    // late_threshold_minutes
        )
        ->withMaxSize(100)
        ->then(function ($timeInHour, $timeOutHour, $threshold) {
            // Create an admin user
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin User',
                'is_active' => true,
            ]);

            // Create an inactive schedule
            $schedule = $this->service->create([
                'name' => 'Inactive Schedule ' . uniqid(),
                'time_in' => sprintf('%02d:00:00', $timeInHour),
                'time_out' => sprintf('%02d:00:00', $timeOutHour),
                'late_threshold_minutes' => $threshold,
                'is_active' => false,  // This schedule is NOT active
                'effective_date' => now()->toDateString(),
            ], $admin->id);

            $scheduleId = $schedule->id;

            // Attempt to delete the inactive schedule
            $result = $this->service->delete($scheduleId, $admin->id);

            // Verify deletion succeeded
            $this->assertTrue(
                $result,
                "Delete operation should succeed for inactive schedule"
            );

            // Verify the record no longer exists in the database
            $existingSchedule = TimeSchedule::find($scheduleId);
            $this->assertNull(
                $existingSchedule,
                "The inactive schedule should be deleted from the database"
            );
        });
    }
}
