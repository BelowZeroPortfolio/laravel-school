<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'student_id' => fake()->unique()->numerify('STU-######'),
            'lrn' => fake()->unique()->numerify('############'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'qrcode_path' => null,
            'photo_path' => null,
            'parent_name' => fake()->name(),
            'parent_phone' => fake()->phoneNumber(),
            'parent_email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'is_active' => true,
            'sms_enabled' => false,
        ];
    }

    /**
     * Indicate that the student is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the student has SMS enabled.
     */
    public function withSms(): static
    {
        return $this->state(fn (array $attributes) => [
            'sms_enabled' => true,
        ]);
    }

    /**
     * Set a specific LRN.
     */
    public function lrn(string $lrn): static
    {
        return $this->state(fn (array $attributes) => [
            'lrn' => $lrn,
        ]);
    }

    /**
     * Assign student to a specific school.
     */
    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }
}
