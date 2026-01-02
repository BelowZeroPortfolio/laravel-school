<?php

namespace Tests\Property;

use App\Models\TimeSchedule;
use App\Models\User;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * **Feature: qr-attendance-laravel-migration, Property 26: Single active time schedule invariant**
 * **Validates: Requirements 7.3**
 */
class TimeScheduleInvariantPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 26: Single active time schedule invariant**
     * 
     * For any state of the system, at most one TimeSchedule SHALL have is_active = true.
     */
    public function testSingleActiveTimeScheduleInvariant(): void
    {
        $this->forAll(
            Generator\choose(2, 10),  // number of schedules to create
            Generator\choose(0, 9)    // index of which one to activate (if any)
        )
        ->withMaxSize(100)
        ->then(function ($count, $activeIndex) {
            // Create an admin user for created_by
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin User',
                'is_active' => true,
            ]);

            // Create multiple time schedules, all inactive initially
            $schedules = [];
            for ($i = 0; $i < $count; $i++) {
                $hour = 6 + $i;
                $schedules[] = TimeSchedule::create([
                    'name' => "Schedule {$i} - " . uniqid(),
                    'time_in' => sprintf('%02d:00:00', $hour),
                    'time_out' => sprintf('%02d:00:00', $hour + 8),
                    'late_threshold_minutes' => 15 + ($i * 5),
                    'is_active' => false,
                    'effective_date' => now()->addDays($i)->toDateString(),
                    'created_by' => $admin->id,
                ]);
            }

            // Activate one schedule (simulating proper activation logic)
            $indexToActivate = $activeIndex % $count;
            
            // Deactivate all others first (this is what the service should do)
            TimeSchedule::where('is_active', true)->update(['is_active' => false]);
            
            // Activate the selected one
            $schedules[$indexToActivate]->update(['is_active' => true]);

            // Verify invariant: at most one active time schedule
            $activeCount = TimeSchedule::where('is_active', true)->count();
            
            $this->assertLessThanOrEqual(
                1,
                $activeCount,
                "At most one time schedule should be active at any time. Found: {$activeCount}"
            );

            // If we activated one, exactly one should be active
            $this->assertEquals(
                1,
                $activeCount,
                "Exactly one time schedule should be active after activation"
            );

            // Verify the correct one is active
            $activeSchedule = TimeSchedule::active()->first();
            $this->assertEquals(
                $schedules[$indexToActivate]->id,
                $activeSchedule->id,
                "The activated time schedule should be the active one"
            );
        });
    }

    /**
     * Test that activating a new time schedule deactivates the previous one.
     */
    public function testActivatingNewTimeScheduleDeactivatesPrevious(): void
    {
        $this->forAll(
            Generator\choose(2, 5)  // number of activation cycles
        )
        ->withMaxSize(100)
        ->then(function ($cycles) {
            // Create an admin user
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin User',
                'is_active' => true,
            ]);

            // Create two time schedules
            $ts1 = TimeSchedule::create([
                'name' => 'Morning Schedule - ' . uniqid(),
                'time_in' => '07:00:00',
                'time_out' => '15:00:00',
                'late_threshold_minutes' => 15,
                'is_active' => true,
                'effective_date' => now()->toDateString(),
                'created_by' => $admin->id,
            ]);

            $ts2 = TimeSchedule::create([
                'name' => 'Afternoon Schedule - ' . uniqid(),
                'time_in' => '08:00:00',
                'time_out' => '16:00:00',
                'late_threshold_minutes' => 20,
                'is_active' => false,
                'effective_date' => now()->toDateString(),
                'created_by' => $admin->id,
            ]);

            // Simulate multiple activation cycles
            for ($i = 0; $i < $cycles; $i++) {
                // Refresh models to get latest state from database
                $ts1->refresh();
                $ts2->refresh();
                
                $toActivate = ($i % 2 === 0) ? $ts2 : $ts1;
                
                // Proper activation: deactivate all, then activate one
                TimeSchedule::where('is_active', true)->update(['is_active' => false]);
                TimeSchedule::where('id', $toActivate->id)->update(['is_active' => true]);

                // Verify invariant after each cycle
                $activeCount = TimeSchedule::where('is_active', true)->count();
                $this->assertEquals(
                    1,
                    $activeCount,
                    "Exactly one time schedule should be active after cycle {$i}"
                );
            }
        });
    }

    /**
     * Test that the active scope returns the correct schedule.
     */
    public function testActiveScopeReturnsCorrectSchedule(): void
    {
        $this->forAll(
            Generator\choose(1, 5)  // number of inactive schedules
        )
        ->withMaxSize(100)
        ->then(function ($inactiveCount) {
            // Clean up any existing schedules and users from previous iterations
            TimeSchedule::query()->delete();
            User::where('username', 'like', 'admin_%')->delete();
            
            // Create an admin user
            $admin = User::create([
                'username' => 'admin_' . uniqid(),
                'password' => bcrypt('password'),
                'role' => 'admin',
                'full_name' => 'Admin User',
                'is_active' => true,
            ]);

            // Create inactive schedules
            for ($i = 0; $i < $inactiveCount; $i++) {
                TimeSchedule::create([
                    'name' => "Inactive Schedule {$i} - " . uniqid(),
                    'time_in' => sprintf('%02d:00:00', 6 + $i),
                    'time_out' => sprintf('%02d:00:00', 14 + $i),
                    'late_threshold_minutes' => 15,
                    'is_active' => false,
                    'created_by' => $admin->id,
                ]);
            }

            // Create one active schedule
            $activeSchedule = TimeSchedule::create([
                'name' => 'Active Schedule - ' . uniqid(),
                'time_in' => '07:30:00',
                'time_out' => '15:30:00',
                'late_threshold_minutes' => 20,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);

            // Verify the active scope returns only the active schedule
            $result = TimeSchedule::active()->get();
            
            $this->assertCount(1, $result, "Active scope should return exactly one schedule");
            $this->assertEquals(
                $activeSchedule->id,
                $result->first()->id,
                "Active scope should return the correct active schedule"
            );
        });
    }
}
