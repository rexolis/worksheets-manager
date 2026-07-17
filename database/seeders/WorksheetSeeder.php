<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LogicException;

class WorksheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjectIds = DB::table('subjects')->pluck('id')->all();
        $worksheetClassIds = DB::table('worksheet_classes')->pluck('id')->all();
        $authorIds = DB::table('user_roles')
            ->where('role_id', 2)
            ->pluck('user_id')
            ->all();

        if ($subjectIds === [] || $worksheetClassIds === []) {
            throw new LogicException('Subjects and worksheet classes must be seeded before worksheets.');
        }

        if ($authorIds === []) {
            throw new LogicException('At least one user with role ID 2 is required to seed worksheets.');
        }

        DB::transaction(function () use ($subjectIds, $worksheetClassIds, $authorIds): void {
            $timestamp = now();
            $worksheetNumber = 1;
            $worksheets = [];

            foreach ($subjectIds as $subjectId) {
                foreach ($worksheetClassIds as $worksheetClassId) {
                    for ($worksheetCount = 0; $worksheetCount < 10; $worksheetCount++) {
                        $worksheets[] = [
                            'number' => $worksheetNumber++,
                            'subject_id' => $subjectId,
                            'subtopic' => $worksheetCount % 2 === 0
                                ? fake()->words(3, true)
                                : null,
                            'title' => fake()->sentence(5),
                            'worksheet_class_id' => $worksheetClassId,
                            'created_by' => Arr::random($authorIds),
                            'updated_by' => null,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }
            }

            DB::table('worksheets')->upsert(
                $worksheets,
                ['number'],
                [
                    'subject_id',
                    'subtopic',
                    'title',
                    'worksheet_class_id',
                    'created_by',
                    'updated_by',
                    'updated_at',
                ],
            );
        });
    }
}
