<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        DB::table('subjects')->upsert([
            [
                'id' => 1,
                'name' => 'English',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'id' => 2,
                'name' => 'Mathematics',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'id' => 3,
                'name' => 'Science',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['id'], ['name', 'updated_at']);
    }
}
