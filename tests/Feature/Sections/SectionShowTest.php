<?php

use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page from a section show page', function () {
    $this->seed(WorksheetClassSeeder::class);

    Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    $this->get(route('sections.show', [
        'worksheetClass' => 'cse',
        'section' => '202601-CSE-A',
    ]))->assertRedirect(route('login'));
});

test('admins can visit a section show page with an empty students placeholder', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    Section::factory()->create([
        'name' => 'Morning Batch A',
        'section_type' => 'Online',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);

    $this->actingAs($admin)
        ->get(route('sections.show', [
            'worksheetClass' => 'cse',
            'section' => '202601-CSE-A',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Show')
            ->where('worksheetClass.slug', 'cse')
            ->where('section.name', 'Morning Batch A')
            ->where('section.section_type', 'Online')
            ->where('section.class_code', '202601-CSE-A')
            ->has('students', 0),
        );
});

test('teachers can visit an assigned section show page', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $teacher = User::factory()->teacher()->create();
    $section = Section::factory()->create([
        'name' => 'Assigned Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    $section->teachers()->attach($teacher);

    $this->actingAs($teacher)
        ->get(route('sections.show', [
            'worksheetClass' => 'cse',
            'section' => '202601-CSE-A',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Show')
            ->where('section.name', 'Assigned Section')
            ->has('students', 0),
        );
});

test('teachers cannot visit sections they are not assigned to', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $teacher = User::factory()->teacher()->create();

    Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    $this->actingAs($teacher)
        ->get(route('sections.show', [
            'worksheetClass' => 'cse',
            'section' => '202601-CSE-A',
        ]))
        ->assertNotFound();
});

test('section show pages return not found for unknown class codes', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('sections.show', [
            'worksheetClass' => 'cse',
            'section' => 'missing-code',
        ]))
        ->assertNotFound();
});

test('section show pages return not found when the section belongs to another class', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    Section::factory()->create([
        'worksheet_class_id' => 2,
        'class_code' => '202603-UPCAT-A',
    ]);

    $this->actingAs($admin)
        ->get(route('sections.show', [
            'worksheetClass' => 'cse',
            'section' => '202603-UPCAT-A',
        ]))
        ->assertNotFound();
});
