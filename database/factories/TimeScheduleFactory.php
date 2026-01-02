<?php

namespace Database\Factories;

use App\Models\TimeSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeSchedule>
 */
class TimeScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TimeSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Schedule',
            'time_in' => '07:30:00',
            'time_out' => '17:00:00',
            'late_threshold_minutes' => fake()->numberBetween(10, 30),
            'is_active' => false,
            'effective_date' => fake()->date(),
            'created_by' => User::factory()->admin(),
        ];
    }

    /**
     * Indicate that the time schedule is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Set a specific time_in value.
     */
    public function timeIn(string $time): static
    {
        return $this->state(fn (array $attributes) => [
            'time_in' => $time,
        ]);
    }

    /**
     * Set a specific late threshold.
     */
    public function lateThreshold(int $minutes): static
    {
        return $this->state(fn (array $attributes) => [
            'late_threshold_minutes' => $minutes,
        ]);
    }
}
