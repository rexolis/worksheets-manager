<?php

use App\Enums\SectionStatusId;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;

test('guests cannot update sections', function () {
    $this->seed(WorksheetClassSeeder::class);

    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);

    $this->put(route('sections.update', $section), [
        'name' => 'Updated Name',
        'section_type' => 'Online',
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ])->assertRedirect(route('login'));
});

test('admins can update a section including review masters', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();
    $teachers = User::factory()->teacher()->count(2)->create();
    $existingTeacher = User::factory()->teacher()->create();

    $section = Section::factory()->create([
        'name' => 'Original Name',
        'section_type' => 'Regular',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);
    $section->teachers()->attach($existingTeacher);

    $this->actingAs($admin)
        ->put(route('sections.update', $section), [
            'name' => 'Updated Name',
            'section_type' => 'Online',
            'class_code' => '202602-CSE-B',
            'date_start' => '2026-02-01',
            'date_end' => '2026-07-01',
            'teacher_ids' => $teachers->pluck('id')->all(),
        ])
        ->assertRedirect(route('sections.show-class', ['worksheetClass' => 'cse']));

    $section->refresh();

    expect($section->name)->toBe('Updated Name')
        ->and($section->section_type)->toBe('Online')
        ->and($section->class_code)->toBe('202602-CSE-B')
        ->and($section->date_start->toDateString())->toBe('2026-02-01')
        ->and($section->date_end->toDateString())->toBe('2026-07-01')
        ->and($section->teachers)->toHaveCount(2)
        ->and($section->teachers->pluck('id')->sort()->values()->all())
        ->toBe($teachers->pluck('id')->sort()->values()->all());
});

test('teachers cannot update sections', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $teacher = User::factory()->teacher()->create();

    $section = Section::factory()->create([
        'name' => 'Original Name',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);
    $section->teachers()->attach($teacher);

    $this->actingAs($teacher)
        ->put(route('sections.update', $section), [
            'name' => 'Updated Name',
            'section_type' => 'Online',
            'class_code' => '202601-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertForbidden();

    expect($section->fresh()->name)->toBe('Original Name');
});

test('updating a section requires a valid class code format for the class and start month', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);

    $this->actingAs($admin)
        ->put(route('sections.update', $section), [
            'name' => 'Updated Name',
            'section_type' => 'Online',
            'class_code' => '202602-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertSessionHasErrors('class_code');
});

test('class codes must remain unique when updating', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);

    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-B',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);

    $this->actingAs($admin)
        ->put(route('sections.update', $section), [
            'name' => 'Updated Name',
            'section_type' => 'Online',
            'class_code' => '202601-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertSessionHasErrors('class_code');
});

test('deleted sections cannot be updated', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    $section = Section::factory()->deleted()->create([
        'name' => 'Deleted Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ]);

    $this->actingAs($admin)
        ->put(route('sections.update', $section), [
            'name' => 'Updated Name',
            'section_type' => 'Online',
            'class_code' => '202601-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertForbidden();

    expect($section->fresh()->name)->toBe('Deleted Section')
        ->and($section->fresh()->status)->toBe(SectionStatusId::Deleted);
});
