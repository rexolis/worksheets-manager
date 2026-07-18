<?php

use App\Models\Subject;
use App\Models\User;
use App\Models\Worksheet;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page from a worksheet class page', function () {
    $this->seed(WorksheetClassSeeder::class);

    $this->get(route('worksheets.show-class', ['worksheetClass' => 'cse']))
        ->assertRedirect(route('login'));
});

test('authenticated admins can visit a worksheet class page and see its subjects', function () {
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
        ->get(route('worksheets.show-class', ['worksheetClass' => 'cse']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Worksheets/Class')
            ->where('worksheetClass.slug', 'cse')
            ->where('worksheetClass.name', 'Civil Service Examination')
            ->has('subjects', 1)
            ->where('subjects.0.slug', 'english')
            ->where('subjects.0.name', 'English'),
        );
});

test('worksheet class pages return not found for unknown slugs', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('worksheets.show-class', ['worksheetClass' => 'missing']))
        ->assertNotFound();
});

test('admins receive worksheet classes in shared inertia props', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('worksheetClasses', 2)
            ->where('worksheetClasses.0.slug', 'cse')
            ->where('worksheetClasses.1.slug', 'upcat'),
        );
});
