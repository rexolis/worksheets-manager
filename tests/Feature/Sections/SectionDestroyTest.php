<?php

use App\Enums\SectionStatusId;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot delete sections', function () {
    $this->seed(WorksheetClassSeeder::class);

    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
    ]);

    $this->delete(route('sections.destroy', $section))
        ->assertRedirect(route('login'));
});

test('admins can soft delete a section', function () {
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
        ->delete(route('sections.destroy', $section))
        ->assertRedirect(route('sections.show-class', ['worksheetClass' => 'cse']));

    expect(Section::query()->find($section->id))->not->toBeNull()
        ->and($section->fresh()->status)->toBe(SectionStatusId::Deleted);
});

test('deleted sections are hidden from the class page', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    Section::factory()->create([
        'name' => 'Visible Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);
    Section::factory()->deleted()->create([
        'name' => 'Deleted Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-B',
    ]);

    $this->actingAs($admin)
        ->get(route('sections.show-class', ['worksheetClass' => 'cse']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Class')
            ->has('sections', 1)
            ->where('sections.0.name', 'Visible Section')
            ->where('sections.0.status', 'active'),
        );
});

test('deleted sections are hidden from the sections index', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    Section::factory()->create([
        'name' => 'Visible Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);
    Section::factory()->deleted()->create([
        'name' => 'Deleted Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-B',
    ]);

    $this->actingAs($admin)
        ->get(route('sections'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Index')
            ->has('sections', 1)
            ->where('sections.0.name', 'Visible Section'),
        );
});

test('teachers cannot delete sections', function () {
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
        ->delete(route('sections.destroy', $section))
        ->assertForbidden();

    expect($section->fresh()->status)->toBe(SectionStatusId::Active);
});

test('already deleted sections cannot be deleted again', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    $section = Section::factory()->deleted()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    $this->actingAs($admin)
        ->delete(route('sections.destroy', $section))
        ->assertForbidden();
});
