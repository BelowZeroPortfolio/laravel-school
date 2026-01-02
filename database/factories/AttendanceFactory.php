<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-30 days', 'now');
        $checkInTime = (clone $date)->setTime(fake()->numberBetween(6, 8), fake()->numberBetween(0, 59));

        return [
            'student_id' => Student::factory(),
            'school_year_id' => SchoolYear::factory(),
            'attendance_date' => $date->format('Y-m-d'),
            'check_in_time' => $checkInTime,
            'check_out_time' => null,
            'status' => 'present',
            'recorded_by' => User::factory()->teacher(),
            'notes' => null,
        ];
    }

    /**
     * Indicate that the attendance is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance_date' => today()->format('Y-m-d'),
            'check_in_time' => now(),
        ]);
    }

    /**
     * Indicate that the student is late.
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
        ]);
    }

    /**
     * Indicate that the student is absent.
     */
    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
            'check_in_time' => null,
        ]);
    }

    /**
     * Indicate that the student has checked out.
     */
    public function withCheckout(): static
    {
        return $this->state(function (array $attributes) {
            $checkIn = $attributes['check_in_time'];
            $checkOut = is_string($checkIn) 
                ? (new \DateTime($checkIn))->modify('+8 hours')
                : (clone $checkIn)->modify('+8 hours');
            
            return [
                'check_out_time' => $checkOut,
            ];
        });
    }

    /**
     * Set a specific attendance date.
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance_date' => $date,
        ]);
    }
}
