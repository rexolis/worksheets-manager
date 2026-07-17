<?php

namespace Database\Factories;

use App\Models\WorksheetClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorksheetClass>
 */
class WorksheetClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }
}
