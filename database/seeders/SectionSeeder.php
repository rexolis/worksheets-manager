<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        DB::table('sections')->upsert([
            [
                'id' => 1,
                'name' => 'Morning Batch A',
                'section_type' => 'Regular',
                'worksheet_class_id' => 1,
                'class_code' => '202601-CSE-A',
                'date_start' => '2026-01-15',
                'date_end' => '2026-06-15',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'id' => 2,
                'name' => 'Evening Batch B',
                'section_type' => 'Intensive',
                'worksheet_class_id' => 1,
                'class_code' => '202602-CSE-B',
                'date_start' => '2026-02-01',
                'date_end' => '2026-07-01',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'id' => 3,
                'name' => 'Review Cohort 1',
                'section_type' => 'Review',
                'worksheet_class_id' => 2,
                'class_code' => '202603-UPCAT-A',
                'date_start' => '2026-03-01',
                'date_end' => '2026-08-31',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['id'], ['name', 'section_type', 'worksheet_class_id', 'class_code', 'date_start', 'date_end', 'updated_at']);
    }
}
