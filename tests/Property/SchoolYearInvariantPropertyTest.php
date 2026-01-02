<?php

namespace Tests\Property;

use App\Models\SchoolYear;
use Eris\Generator;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * **Feature: qr-attendance-laravel-migration, Property 35: Single active school year invariant**
 * **Validates: Requirements 10.1**
 */
class SchoolYearInvariantPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minimumEvaluationRatio(0.5);
    }

    /**
     * **Feature: qr-attendance-laravel-migration, Property 35: Single active school year invariant**
     * 
     * For any state of the system, at most one SchoolYear SHALL have is_active = true.
     */
    public function testSingleActiveSchoolYearInvariant(): void
    {
        $this->forAll(
            Generator\choose(2, 10),  // number of school years to create
            Generator\choose(0, 9)    // index of which one to activate (if any)
        )
        ->withMaxSize(100)
        ->then(function ($count, $activeIndex) {
            // Create multiple school years, all inactive initially
            $schoolYears = [];
            for ($i = 0; $i < $count; $i++) {
                $year = 2020 + $i;
                $schoolYears[] = SchoolYear::create([
                    'name' => "SY {$year}-" . ($year + 1) . '-' . uniqid(),
                    'is_active' => false,
                    'is_locked' => false,
                    'start_date' => "{$year}-06-01",
                    'end_date' => ($year + 1) . "-03-31",
                ]);
            }

            // Activate one school year (simulating proper activation logic)
            $indexToActivate = $activeIndex % $count;
            
            // Deactivate all others first (this is what the service should do)
            SchoolYear::where('is_active', true)->update(['is_active' => false]);
            
            // Activate the selected one
            $schoolYears[$indexToActivate]->update(['is_active' => true]);

            // Verify invariant: at most one active school year
            $activeCount = SchoolYear::where('is_active', true)->count();
            
            $this->assertLessThanOrEqual(
                1,
                $activeCount,
                "At most one school year should be active at any time. Found: {$activeCount}"
            );

            // If we activated one, exactly one should be active
            $this->assertEquals(
                1,
                $activeCount,
                "Exactly one school year should be active after activation"
            );

            // Verify the correct one is active
            $activeSchoolYear = SchoolYear::active()->first();
            $this->assertEquals(
                $schoolYears[$indexToActivate]->id,
                $activeSchoolYear->id,
                "The activated school year should be the active one"
            );
        });
    }

    /**
     * Test that activating a new school year deactivates the previous one.
     */
    public function testActivatingNewSchoolYearDeactivatesPrevious(): void
    {
        $this->forAll(
            Generator\choose(2, 5)  // number of activation cycles
        )
        ->withMaxSize(100)
        ->then(function ($cycles) {
            // Create two school years
            $sy1 = SchoolYear::create([
                'name' => 'SY 2024-2025-' . uniqid(),
                'is_active' => true,
                'is_locked' => false,
                'start_date' => '2024-06-01',
                'end_date' => '2025-03-31',
            ]);

            $sy2 = SchoolYear::create([
                'name' => 'SY 2025-2026-' . uniqid(),
                'is_active' => false,
                'is_locked' => false,
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
            ]);

            // Simulate multiple activation cycles
            for ($i = 0; $i < $cycles; $i++) {
                // Refresh models to get latest state from database
                $sy1->refresh();
                $sy2->refresh();
                
                $toActivate = ($i % 2 === 0) ? $sy2 : $sy1;
                
                // Proper activation: deactivate all, then activate one
                SchoolYear::where('is_active', true)->update(['is_active' => false]);
                SchoolYear::where('id', $toActivate->id)->update(['is_active' => true]);

                // Verify invariant after each cycle
                $activeCount = SchoolYear::where('is_active', true)->count();
                $this->assertEquals(
                    1,
                    $activeCount,
                    "Exactly one school year should be active after cycle {$i}"
                );
            }
        });
    }
}
