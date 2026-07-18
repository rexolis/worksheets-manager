<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use App\Models\Worksheet;
use App\Models\WorksheetClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Worksheet>
 */
class WorksheetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numberBetween(1, 999999),
            'subject_id' => Subject::factory(),
            'subtopic' => fake()->optional()->words(3, true),
            'title' => fake()->sentence(5),
            'worksheet_class_id' => WorksheetClass::factory(),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
