<?php

use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\DB;

test('it creates users with valid random role assignments', function () {
    $this->seed([
        RoleSeeder::class,
        UserSeeder::class,
    ]);

    $this->assertDatabaseCount('users', 10);
    $this->assertDatabaseCount('user_roles', 10);

    expect(DB::table('user_roles')->distinct()->count('user_id'))->toBe(10)
        ->and(DB::table('user_roles')->whereNotIn('role_id', [1, 2])->count())->toBe(0);
});
