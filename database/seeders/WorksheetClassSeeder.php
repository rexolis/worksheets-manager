<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorksheetClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        DB::table('worksheet_classes')->upsert([
            [
                'id' => 1,
                'name' => 'Civil Service Examination',
                'slug' => 'cse',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'id' => 2,
                'name' => 'University of the Philippines College Admission Test',
                'slug' => 'upcat',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['id'], ['name', 'slug', 'updated_at']);
    }
}
