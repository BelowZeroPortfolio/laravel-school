<?php

namespace Database\Factories;

use App\Models\SchoolYear;
use App\Models\TeacherAttendance;
use App\Models\TimeSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeacherAttendance>
 */
class TeacherAttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TeacherAttendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-30 days', 'now');
        $timeIn = (clone $date)->setTime(fake()->numberBetween(6, 8), fake()->numberBetween(0, 59));

        return [
            'teacher_id' => User::factory()->teacher(),
            'school_year_id' => SchoolYear::factory(),
            'attendance_date' => $date->format('Y-m-d'),
            'time_in' => $timeIn,
            'time_out' => null,
            'first_student_scan' => null,
            'attendance_status' => 'pending',
            'late_status' => null,
            'time_rule_id' => null,
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
            'time_in' => now(),
        ]);
    }

    /**
     * Indicate that the attendance status is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance_status' => 'pending',
            'late_status' => null,
            'first_student_scan' => null,
            'time_rule_id' => null,
        ]);
    }

    /**
     * Indicate that the attendance status is confirmed (on time).
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance_status' => 'confirmed',
            'late_status' => 'on_time',
            'first_student_scan' => $attributes['time_in'] ?? now(),
            'time_rule_id' => TimeSchedule::factory(),
        ]);
    }

    /**
     * Indicate that the attendance status is late.
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance_status' => 'late',
            'late_status' => 'late',
            'first_student_scan' => $attributes['time_in'] ?? now(),
            'time_rule_id' => TimeSchedule::factory(),
        ]);
    }

    /**
     * Indicate that the attendance status is absent.
     */
    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance_status' => 'absent',
            'late_status' => null,
            'time_in' => null,
            'first_student_scan' => null,
            'time_rule_id' => null,
        ]);
    }

    /**
     * Indicate that the attendance status is no_scan.
     */
    public function noScan(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance_status' => 'no_scan',
            'late_status' => null,
            'first_student_scan' => null,
        ]);
    }

    /**
     * Indicate that the teacher has logged out.
     */
    public function withLogout(): static
    {
        return $this->state(function (array $attributes) {
            $timeIn = $attributes['time_in'];
            $timeOut = is_string($timeIn)
                ? (new \DateTime($timeIn))->modify('+8 hours')
                : (clone $timeIn)->modify('+8 hours');

            return [
                'time_out' => $timeOut,
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

    /**
     * Set a specific time rule.
     */
    public function withTimeRule(TimeSchedule|int $timeRule): static
    {
        return $this->state(fn (array $attributes) => [
            'time_rule_id' => $timeRule instanceof TimeSchedule ? $timeRule->id : $timeRule,
        ]);
    }
}
