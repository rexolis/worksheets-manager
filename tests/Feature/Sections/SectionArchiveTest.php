<?php

use App\Enums\SectionStatusId;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot archive sections', function () {
    $this->seed(WorksheetClassSeeder::class);

    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
    ]);

    $this->post(route('sections.archive', $section))
        ->assertRedirect(route('login'));
});

test('admins can archive a section', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    $section = Section::factory()->create([
        'name' => 'Active Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    $this->actingAs($admin)
        ->post(route('sections.archive', $section))
        ->assertRedirect(route('sections.show-class', ['worksheetClass' => 'cse']));

    expect($section->fresh()->status)->toBe(SectionStatusId::Archived);
});

test('archived sections remain visible on the class page', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    Section::factory()->archived()->create([
        'name' => 'Archived Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    $this->actingAs($admin)
        ->get(route('sections.show-class', ['worksheetClass' => 'cse']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Class')
            ->has('sections', 1)
            ->where('sections.0.name', 'Archived Section')
            ->where('sections.0.status', 'archived'),
        );
});

test('teachers cannot archive sections', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $teacher = User::factory()->teacher()->create();

    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);
    $section->teachers()->attach($teacher);

    $this->actingAs($teacher)
        ->post(route('sections.archive', $section))
        ->assertForbidden();

    expect($section->fresh()->status)->toBe(SectionStatusId::Active);
});

test('already archived sections cannot be archived again', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    $section = Section::factory()->archived()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    $this->actingAs($admin)
        ->post(route('sections.archive', $section))
        ->assertForbidden();
});
