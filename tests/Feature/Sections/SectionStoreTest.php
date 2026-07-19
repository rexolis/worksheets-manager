<?php

use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot store sections', function () {
    $this->seed(WorksheetClassSeeder::class);

    $this->post(route('sections.store'), [
        'name' => 'Morning Batch A',
        'section_type' => 'Regular',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'date_start' => '2026-01-15',
        'date_end' => '2026-06-15',
    ])->assertRedirect(route('login'));
});

test('admins can store a section and are redirected to the class page', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('sections.store'), [
            'name' => 'Morning Batch A',
            'section_type' => 'Regular',
            'worksheet_class_id' => 1,
            'class_code' => '202601-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertRedirect(route('sections.show-class', ['worksheetClass' => 'cse']));

    $section = Section::query()->where('class_code', '202601-CSE-A')->first();

    expect($section)->not->toBeNull()
        ->and($section->name)->toBe('Morning Batch A')
        ->and($section->section_type)->toBe('Regular')
        ->and($section->worksheet_class_id)->toBe(1)
        ->and($section->date_start->toDateString())->toBe('2026-01-15')
        ->and($section->date_end->toDateString())->toBe('2026-06-15');
});

test('storing a section requires a section type', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('sections.store'), [
            'name' => 'Morning Batch A',
            'worksheet_class_id' => 1,
            'class_code' => '202601-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertSessionHasErrors('section_type');

    expect(Section::query()->count())->toBe(0);
});

test('teachers cannot store sections', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->post(route('sections.store'), [
            'name' => 'Morning Batch A',
            'section_type' => 'Regular',
            'worksheet_class_id' => 1,
            'class_code' => '202601-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertForbidden();

    expect(Section::query()->count())->toBe(0);
});

test('storing a section requires a valid class code format for the class and start month', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('sections.store'), [
            'name' => 'Morning Batch A',
            'section_type' => 'Regular',
            'worksheet_class_id' => 1,
            'class_code' => 'CSE-AM-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertSessionHasErrors('class_code');

    $this->actingAs($admin)
        ->post(route('sections.store'), [
            'name' => 'Morning Batch A',
            'section_type' => 'Regular',
            'worksheet_class_id' => 1,
            'class_code' => '202602-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertSessionHasErrors('class_code');
});

test('class codes must be unique', function () {
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

    $this->actingAs($admin)
        ->post(route('sections.store'), [
            'name' => 'Another Section',
            'section_type' => 'Regular',
            'worksheet_class_id' => 1,
            'class_code' => '202601-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
        ])
        ->assertSessionHasErrors('class_code');
});

test('admins can assign review masters when storing a section', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();
    $teachers = User::factory()->teacher()->count(2)->create();
    User::factory()->create();

    $this->actingAs($admin)
        ->post(route('sections.store'), [
            'name' => 'Morning Batch A',
            'section_type' => 'Regular',
            'worksheet_class_id' => 1,
            'class_code' => '202601-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
            'teacher_ids' => $teachers->pluck('id')->all(),
        ])
        ->assertRedirect(route('sections.show-class', ['worksheetClass' => 'cse']));

    $section = Section::query()->where('class_code', '202601-CSE-A')->first();

    expect($section)->not->toBeNull()
        ->and($section->teachers)->toHaveCount(2)
        ->and($section->teachers->pluck('id')->sort()->values()->all())
        ->toBe($teachers->pluck('id')->sort()->values()->all());
});

test('non-teacher users cannot be assigned as review masters', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();
    $regularUser = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('sections.store'), [
            'name' => 'Morning Batch A',
            'section_type' => 'Regular',
            'worksheet_class_id' => 1,
            'class_code' => '202601-CSE-A',
            'date_start' => '2026-01-15',
            'date_end' => '2026-06-15',
            'teacher_ids' => [$regularUser->id],
        ])
        ->assertSessionHasErrors('teacher_ids.0');

    expect(Section::query()->count())->toBe(0);
});

test('admins receive review masters on the section class page', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create([
        'name' => 'Review Master One',
        'email' => 'rm1@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('sections.show-class', ['worksheetClass' => 'cse']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sections/Class')
            ->has('teachers', 1)
            ->where('teachers.0.id', $teacher->id)
            ->where('teachers.0.name', 'Review Master One')
            ->where('teachers.0.email', 'rm1@example.com'),
        );
});
