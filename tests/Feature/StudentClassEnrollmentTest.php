<?php

use App\Enums\SectionStatusId;
use App\Enums\StudentClassStatus;
use App\Models\Section;
use App\Models\StudentClass;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;

test('users can enroll in an active section with a class code', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $user = User::factory()->create();
    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    $this->actingAs($user)
        ->post(route('classes.store'), [
            'class_code' => '202601-CSE-A',
        ])
        ->assertRedirect(route('classes'));

    $this->assertDatabaseHas('student_class', [
        'user_id' => $user->id,
        'section_id' => $section->id,
        'status' => StudentClassStatus::Pending->value,
    ]);
});

test('users cannot enroll with an invalid class code', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('classes'))
        ->post(route('classes.store'), [
            'class_code' => 'missing-code',
        ])
        ->assertRedirect(route('classes'))
        ->assertSessionHasErrors('class_code');
});

test('users cannot enroll in archived sections', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $user = User::factory()->create();

    Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
        'status' => SectionStatusId::Archived,
    ]);

    $this->actingAs($user)
        ->from(route('classes'))
        ->post(route('classes.store'), [
            'class_code' => '202601-CSE-A',
        ])
        ->assertRedirect(route('classes'))
        ->assertSessionHasErrors('class_code');
});

test('users cannot enroll twice in the same section', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $user = User::factory()->create();
    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    StudentClass::factory()->pending()->create([
        'user_id' => $user->id,
        'section_id' => $section->id,
    ]);

    $this->actingAs($user)
        ->from(route('classes'))
        ->post(route('classes.store'), [
            'class_code' => '202601-CSE-A',
        ])
        ->assertRedirect(route('classes'))
        ->assertSessionHasErrors('class_code');
});

test('denied users can re-enroll and return to pending', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $user = User::factory()->create();
    $section = Section::factory()->create([
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);

    $enrollment = StudentClass::factory()->denied()->create([
        'user_id' => $user->id,
        'section_id' => $section->id,
    ]);

    $this->actingAs($user)
        ->post(route('classes.store'), [
            'class_code' => '202601-CSE-A',
        ])
        ->assertRedirect(route('classes'));

    expect($enrollment->fresh()->status)->toBe(StudentClassStatus::Pending);
});
