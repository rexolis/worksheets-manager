<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

test('teachers cannot visit the worksheets index', function () {
    $this->seed(RoleSeeder::class);

    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('worksheets'))
        ->assertForbidden();
});
