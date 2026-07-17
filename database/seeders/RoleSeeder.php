<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        DB::table('roles')->upsert([
            [
                'id' => 1,
                'name' => 'Regular User',
                'slug' => 'user',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'id' => 2,
                'name' => 'Administrator',
                'slug' => 'admin',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['id'], ['name', 'slug', 'updated_at']);
    }
}
