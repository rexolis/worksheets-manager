<?php

use App\Enums\StudentClassStatus;
use App\Models\Section;
use App\Models\StudentClass;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admins can view any student class records', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('viewAny', StudentClass::class))->toBeTrue();
});

test('teachers can view any student class records', function () {
    $teacher = User::factory()->teacher()->create();

    expect($teacher->can('viewAny', StudentClass::class))->toBeTrue();
});

test('regular users can view any student class records', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', StudentClass::class))->toBeTrue();
});

test('admins can view a student class enrollment', function () {
    $admin = User::factory()->admin()->create();
    $studentClass = StudentClass::factory()->create();

    expect($admin->can('view', $studentClass))->toBeTrue();
});

test('enrolled students can view their own enrollment', function () {
    $student = User::factory()->create();
    $studentClass = StudentClass::factory()->for($student)->create();

    expect($student->can('view', $studentClass))->toBeTrue();
});

test('teachers can view enrollments for their assigned section', function () {
    $teacher = User::factory()->teacher()->create();
    $section = Section::factory()->create();
    $section->teachers()->attach($teacher);

    $studentClass = StudentClass::factory()->for($section)->create();

    expect($teacher->can('view', $studentClass))->toBeTrue();
});

test('teachers cannot view enrollments for unassigned sections', function () {
    $teacher = User::factory()->teacher()->create();
    $studentClass = StudentClass::factory()->create();

    expect($teacher->can('view', $studentClass))->toBeFalse();
});

test('authenticated users can create student class records while only admins can update or delete them', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->create();
    $studentClass = StudentClass::factory()->for($student)->create();

    expect($admin->can('create', StudentClass::class))->toBeTrue()
        ->and($admin->can('update', $studentClass))->toBeTrue()
        ->and($admin->can('delete', $studentClass))->toBeTrue()
        ->and($teacher->can('create', StudentClass::class))->toBeTrue()
        ->and($teacher->can('update', $studentClass))->toBeFalse()
        ->and($teacher->can('delete', $studentClass))->toBeFalse()
        ->and($student->can('create', StudentClass::class))->toBeTrue()
        ->and($student->can('update', $studentClass))->toBeFalse()
        ->and($student->can('delete', $studentClass))->toBeFalse();
});

test('student class status is cast to the enum', function () {
    $studentClass = StudentClass::factory()->approved()->create();

    expect($studentClass->status)->toBe(StudentClassStatus::Approved)
        ->and($studentClass->user)->toBeInstanceOf(User::class)
        ->and($studentClass->section)->toBeInstanceOf(Section::class);
});
