<?php

use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('teachers can visit the sections index and only see assigned sections', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $teacher = User::factory()->teacher()->create();

    $assigned = Section::factory()->create([
        'name' => 'Assigned Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);
    Section::factory()->create([
        'name' => 'Other Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-B',
    ]);

    $assigned->teachers()->attach($teacher);

    $this->actingAs($teacher)
        ->get(route('sections'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Index')
            ->has('sections', 1)
            ->where('sections.0.name', 'Assigned Section')
            ->where('sections.0.class_code', '202601-CSE-A'),
        );
});

test('a section can have multiple teachers assigned', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $teachers = User::factory()->teacher()->count(2)->create();
    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
    ]);

    $section->teachers()->attach($teachers->pluck('id'));

    expect($section->teachers)->toHaveCount(2)
        ->and($teachers[0]->fresh()->isTeacher())->toBeTrue()
        ->and($teachers[1]->fresh()->sections)->toHaveCount(1);
});

test('admins can still see all sections including unassigned ones', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();

    $assigned = Section::factory()->create([
        'name' => 'Teacher Section',
        'worksheet_class_id' => 1,
        'class_code' => '202602-CSE-A',
    ]);
    Section::factory()->create([
        'name' => 'Unassigned Section',
        'worksheet_class_id' => 1,
        'class_code' => '202602-CSE-B',
    ]);

    $assigned->teachers()->attach($teacher);

    $this->actingAs($admin)
        ->get(route('sections'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Index')
            ->has('sections', 2),
        );
});
