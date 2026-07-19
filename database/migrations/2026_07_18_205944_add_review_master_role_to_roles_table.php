<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $timestamp = now();

        DB::table('roles')->updateOrInsert(
            ['id' => 3],
            [
                'name' => 'Review Master',
                'slug' => 'teacher',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('id', 3)->where('slug', 'teacher')->delete();
    }
};
