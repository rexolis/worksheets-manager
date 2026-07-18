<?php

use App\Models\Subject;
use App\Models\User;
use App\Models\Worksheet;
use App\Models\WorksheetClass;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Database\Seeders\WorksheetSeeder;
use Illuminate\Support\Facades\DB;

test('it seeds worksheet reference data without duplicates', function () {
    $this->seed([
        SubjectSeeder::class,
        WorksheetClassSeeder::class,
    ]);
    $this->seed([
        SubjectSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $this->assertDatabaseCount('subjects', 3);
    $this->assertDatabaseHas('subjects', ['id' => 1, 'name' => 'English']);
    $this->assertDatabaseHas('subjects', ['id' => 2, 'name' => 'Mathematics']);
    $this->assertDatabaseHas('subjects', ['id' => 3, 'name' => 'Science']);

    $this->assertDatabaseCount('worksheet_classes', 2);
    $this->assertDatabaseHas('worksheet_classes', [
        'id' => 1,
        'name' => 'Civil Service Examination',
        'slug' => 'cse',
    ]);
    $this->assertDatabaseHas('worksheet_classes', [
        'id' => 2,
        'name' => 'University of the Philippines College Admission Test',
        'slug' => 'upcat',
    ]);
});

test('a worksheet belongs to its subject class and creator', function () {
    $creator = User::factory()->create();
    $worksheet = Worksheet::factory()
        ->recycle($creator)
        ->create();

    expect($worksheet->subject)->toBeInstanceOf(Subject::class)
        ->and($worksheet->worksheetClass)->toBeInstanceOf(WorksheetClass::class)
        ->and($worksheet->creator->is($creator))->toBeTrue()
        ->and($worksheet->updater)->toBeNull();
});

test('it seeds ten worksheets per subject and class using only role two authors', function () {
    $this->seed([
        RoleSeeder::class,
        SubjectSeeder::class,
        WorksheetClassSeeder::class,
    ]);

    $authors = User::factory()->count(2)->create();
    $nonAuthor = User::factory()->create();
    $timestamp = now();

    DB::table('user_roles')->insert([
        [
            'user_id' => $authors[0]->id,
            'role_id' => 2,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'user_id' => $authors[1]->id,
            'role_id' => 2,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'user_id' => $nonAuthor->id,
            'role_id' => 1,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
    ]);

    $this->seed(WorksheetSeeder::class);
    $this->seed(WorksheetSeeder::class);

    $authorIds = $authors->modelKeys();

    $this->assertDatabaseCount('worksheets', 60);
    expect(Worksheet::query()->whereNotIn('created_by', $authorIds)->doesntExist())->toBeTrue()
        ->and(Worksheet::query()->whereNull('subtopic')->count())->toBe(30)
        ->and(Worksheet::query()->whereNotNull('subtopic')->count())->toBe(30);

    foreach (Subject::query()->get() as $subject) {
        foreach (WorksheetClass::query()->get() as $worksheetClass) {
            $numbers = Worksheet::query()
                ->whereBelongsTo($subject)
                ->whereBelongsTo($worksheetClass)
                ->orderBy('number')
                ->pluck('number')
                ->all();

            expect($numbers)->toBe(range(1, 10));
        }
    }
});
