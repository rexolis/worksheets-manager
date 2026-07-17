<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('worksheets'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the worksheets index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('worksheets'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Worksheets/Index'),
        );
});
