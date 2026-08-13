<?php

use App\Enums\SectionStatusId;
use App\Enums\StudentClassStatus;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SectionSeeder;
use Database\Seeders\SectionStatusSeeder;
use Database\Seeders\StudentClassSeeder;
use Database\Seeders\WorksheetClassSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

test('it creates ten regular users and enrolls them in an active section', function () {
    $this->seed([
        RoleSeeder::class,
        SectionStatusSeeder::class,
        WorksheetClassSeeder::class,
        SectionSeeder::class,
        StudentClassSeeder::class,
    ]);

    $emails = [
        'avery.chen@example.com',
        'jordan.blake@example.com',
        'morgan.reyes@example.com',
        'casey.nguyen@example.com',
        'riley.patel@example.com',
        'quinn.morales@example.com',
        'harper.singh@example.com',
        'reese.ortiz@example.com',
        'cameron.kim@example.com',
        'taylor.brooks@example.com',
    ];

    $users = DB::table('users')->whereIn('email', $emails)->get();

    expect($users)->toHaveCount(10);

    $userIds = $users->pluck('id')->all();

    expect(DB::table('user_roles')->whereIn('user_id', $userIds)->where('role_id', 1)->count())->toBe(10)
        ->and(DB::table('student_class')->whereIn('user_id', $userIds)->count())->toBe(10)
        ->and(DB::table('student_class')->whereIn('user_id', $userIds)->distinct()->count('section_id'))->toBe(1)
        ->and(DB::table('student_class')->whereIn('user_id', $userIds)->where('status', StudentClassStatus::Approved->value)->count())->toBe(10);

    $sectionId = DB::table('student_class')->whereIn('user_id', $userIds)->value('section_id');

    expect(DB::table('sections')->where('id', $sectionId)->value('status'))->toBe(SectionStatusId::Active->value)
        ->and(Hash::check('password', $users->first()->password))->toBeTrue();
});

test('it is idempotent when run more than once', function () {
    $this->seed([
        RoleSeeder::class,
        SectionStatusSeeder::class,
        WorksheetClassSeeder::class,
        SectionSeeder::class,
        StudentClassSeeder::class,
        StudentClassSeeder::class,
    ]);

    expect(DB::table('users')->where('email', 'avery.chen@example.com')->count())->toBe(1)
        ->and(DB::table('student_class')->count())->toBe(10);
});
