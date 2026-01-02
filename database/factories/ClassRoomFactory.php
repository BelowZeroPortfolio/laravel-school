<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassRoom>
 */
class ClassRoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClassRoom::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gradeLevels = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 
                        'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
        $sections = ['A', 'B', 'C', 'D', 'E', 'F'];

        return [
            'grade_level' => fake()->randomElement($gradeLevels),
            'section' => fake()->randomElement($sections),
            'teacher_id' => User::factory()->teacher(),
            'school_year_id' => SchoolYear::factory(),
            'is_active' => true,
            'max_capacity' => fake()->numberBetween(30, 50),
        ];
    }

    /**
     * Indicate that the class is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific grade level.
     */
    public function gradeLevel(string $gradeLevel): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_level' => $gradeLevel,
        ]);
    }

    /**
     * Set a specific section.
     */
    public function section(string $section): static
    {
        return $this->state(fn (array $attributes) => [
            'section' => $section,
        ]);
    }

    /**
     * Set a specific max capacity.
     */
    public function capacity(int $capacity): static
    {
        return $this->state(fn (array $attributes) => [
            'max_capacity' => $capacity,
        ]);
    }
}
