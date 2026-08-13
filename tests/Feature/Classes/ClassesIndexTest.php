<?php

use App\Models\Section;
use App\Models\StudentClass;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from the classes page', function () {
    $this->get(route('classes'))->assertRedirect(route('login'));
});

test('users can view their pending and approved enrollments grouped by worksheet class', function () {
    $this->seed([
        RoleSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $user = User::factory()->create();
    $pendingSection = Section::factory()->create([
        'name' => 'Pending Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-A',
    ]);
    $approvedSection = Section::factory()->create([
        'name' => 'Approved Section',
        'worksheet_class_id' => 1,
        'class_code' => '202601-CSE-B',
    ]);
    $deniedSection = Section::factory()->create([
        'name' => 'Denied Section',
        'worksheet_class_id' => 2,
        'class_code' => '202603-UPCAT-A',
    ]);

    StudentClass::factory()->pending()->create([
        'user_id' => $user->id,
        'section_id' => $pendingSection->id,
    ]);
    StudentClass::factory()->approved()->create([
        'user_id' => $user->id,
        'section_id' => $approvedSection->id,
    ]);
    StudentClass::factory()->denied()->create([
        'user_id' => $user->id,
        'section_id' => $deniedSection->id,
    ]);

    $this->actingAs($user)
        ->get(route('classes'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Classes/Index')
            ->has('classes', 1)
            ->where('classes.0.slug', 'cse')
            ->has('classes.0.sections', 2)
            ->where('classes.0.sections.0.name', 'Approved Section')
            ->where('classes.0.sections.0.status', 'approved')
            ->where('classes.0.sections.1.name', 'Pending Section')
            ->where('classes.0.sections.1.status', 'pending'),
        );
});
