<?php

use Database\Seeders\RoleSeeder;

test('it seeds the initial roles without duplicates', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    $this->assertDatabaseCount('roles', 2);
    $this->assertDatabaseHas('roles', [
        'id' => 1,
        'name' => 'Regular User',
        'slug' => 'user',
    ]);
    $this->assertDatabaseHas('roles', [
        'id' => 2,
        'name' => 'Administrator',
        'slug' => 'admin',
    ]);
});
