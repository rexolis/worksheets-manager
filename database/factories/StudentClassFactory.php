<?php

namespace Database\Factories;

use App\Enums\StudentClassStatus;
use App\Models\Section;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentClass>
 */
class StudentClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'section_id' => Section::factory(),
            'status' => StudentClassStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => StudentClassStatus::Approved,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => StudentClassStatus::Pending,
        ]);
    }

    public function denied(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => StudentClassStatus::Denied,
        ]);
    }
}
