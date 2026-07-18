<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('worksheets'))
        ->assertRedirect(route('login'));
});

test('authenticated admins can visit the worksheets index', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('worksheets'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Worksheets/Index'),
        );
});
