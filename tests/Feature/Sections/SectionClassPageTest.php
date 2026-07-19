<?php

use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page from a section class page', function () {
    $this->seed(WorksheetClassSeeder::class);

    $this->get(route('sections.show-class', ['worksheetClass' => 'cse']))
        ->assertRedirect(route('login'));
});

test('authenticated admins can visit a section class page and see its sections', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $user = User::factory()->admin()->create();

    Section::factory()->create([
        'name' => 'Morning Batch A',
        'section_type' => 'Online',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);
    Section::factory()->create([
        'name' => 'UPCAT Cohort',
        'worksheet_class_id' => 2,
        'class_code' => '202603-UPCAT-A',
    ]);

    $this->actingAs($user)
        ->get(route('sections.show-class', ['worksheetClass' => 'cse']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Class')
            ->where('worksheetClass.slug', 'cse')
            ->where('worksheetClass.name', 'Civil Service Examination')
            ->has('sections', 1)
            ->where('sections.0.name', 'Morning Batch A')
            ->where('sections.0.section_type', 'Online')
            ->where('sections.0.class_code', '202601-CSE-A'),
        );
});

test('teachers only see assigned sections on a section class page', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $teacher = User::factory()->teacher()->create();

    $assigned = Section::factory()->create([
        'name' => 'Assigned CSE Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);
    Section::factory()->create([
        'name' => 'Other CSE Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-B',
    ]);

    $assigned->teachers()->attach($teacher);

    $this->actingAs($teacher)
        ->get(route('sections.show-class', ['worksheetClass' => 'cse']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Class')
            ->has('sections', 1)
            ->where('sections.0.name', 'Assigned CSE Section'),
        );
});

test('section class pages return not found for unknown slugs', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('sections.show-class', ['worksheetClass' => 'missing']))
        ->assertNotFound();
});

test('teachers receive worksheet classes in shared inertia props', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $user = User::factory()->teacher()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('worksheetClasses', 2)
            ->where('worksheetClasses.0.slug', 'cse')
            ->where('worksheetClasses.1.slug', 'upcat'),
        );
});
