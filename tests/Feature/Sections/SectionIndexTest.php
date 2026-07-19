<?php

use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('sections'))
        ->assertRedirect(route('login'));
});

test('authenticated admins can visit the sections index', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $user = User::factory()->admin()->create();

    Section::factory()->create([
        'name' => 'Morning Batch A',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);

    $this->actingAs($user)
        ->get(route('sections'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Index')
            ->has('sections', 1)
            ->where('sections.0.name', 'Morning Batch A')
            ->where('sections.0.class_code', '202601-CSE-A')
            ->where('sections.0.date_start', '2026-01-15')
            ->where('sections.0.date_end', '2026-06-15')
            ->where('sections.0.worksheet_class.slug', 'cse')
            ->where('sections.0.worksheet_class.name', 'Civil Service Examination'),
        );
});

test('users without admin or teacher roles cannot visit the sections index', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('sections'))
        ->assertForbidden();
});
