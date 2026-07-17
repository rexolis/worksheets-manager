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
                'name' => 'CSE',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'id' => 2,
                'name' => 'UPCAT',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['id'], ['name', 'updated_at']);
    }
}
