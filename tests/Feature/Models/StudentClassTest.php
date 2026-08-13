<?php

use App\Enums\StudentClassStatus;
use App\Models\Section;
use App\Models\StudentClass;
use App\Models\User;

test('a student class belongs to a user and section', function () {
    $user = User::factory()->create();
    $section = Section::factory()->create();

    $studentClass = StudentClass::factory()->create([
        'user_id' => $user->id,
        'section_id' => $section->id,
        'status' => StudentClassStatus::Approved,
    ]);

    expect($studentClass->user->is($user))->toBeTrue()
        ->and($studentClass->section->is($section))->toBeTrue()
        ->and($user->studentClasses)->toHaveCount(1)
        ->and($section->studentClasses)->toHaveCount(1)
        ->and($user->enrolledSections->first()->is($section))->toBeTrue()
        ->and($section->students->first()->is($user))->toBeTrue();
});
