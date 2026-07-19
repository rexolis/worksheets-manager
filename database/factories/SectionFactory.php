<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\WorksheetClass;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dateStart = fake()->dateTimeBetween('-1 year', '+1 month');
        $dateEnd = fake()->dateTimeBetween($dateStart, '+1 year');

        return [
            'name' => fake()->unique()->words(2, true),
            'section_type' => fake()->randomElement(['Regular', 'Intensive', 'Review']),
            'worksheet_class_id' => WorksheetClass::factory(),
            'class_code' => fn (array $attributes): string => $this->classCode($attributes),
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function classCode(array $attributes): string
    {
        $yearMonth = Carbon::parse($attributes['date_start'])->format('Ym');
        $slug = strtoupper(
            WorksheetClass::query()->findOrFail($attributes['worksheet_class_id'])->slug
        );

        return fake()->unique()->passthrough(sprintf(
            '%s-%s-%s',
            $yearMonth,
            $slug,
            fake()->randomElement(range('A', 'Z')),
        ));
    }
}
