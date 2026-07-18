<?php

use App\Models\Subject;
use App\Models\User;
use App\Models\Worksheet;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('worksheets index uses stored worksheet class slugs', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
        SubjectSeeder::class,
    ]);

    $subject = Subject::query()->where('name', 'English')->firstOrFail();
    $user = User::factory()->admin()->create();

    Worksheet::factory()->create([
        'worksheet_class_id' => 1,
        'subject_id' => $subject->id,
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('worksheets'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Worksheets/Index')
            ->where('classes.0.slug', 'cse')
            ->where('classes.0.name', 'Civil Service Examination')
            ->where('classes.1.slug', 'upcat')
            ->where('classes.1.name', 'University of the Philippines College Admission Test'),
        );
});

test('subject pages resolve worksheet classes by stored slug', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
        SubjectSeeder::class,
    ]);

    $subject = Subject::query()->where('name', 'English')->firstOrFail();
    $user = User::factory()->admin()->create();

    Worksheet::factory()->create([
        'worksheet_class_id' => 1,
        'subject_id' => $subject->id,
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('worksheets.subject', [
            'worksheetClass' => 'cse',
            'subject' => 'english',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Worksheets/Subject')
            ->where('worksheetClass.slug', 'cse')
            ->where('worksheetClass.name', 'Civil Service Examination')
            ->where('subject.slug', 'english'),
        );
});
