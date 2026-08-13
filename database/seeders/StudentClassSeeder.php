<?php

namespace Database\Seeders;

use App\Enums\SectionStatusId;
use App\Enums\StudentClassStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class StudentClassSeeder extends Seeder
{
    /**
     * @var list<array{name: string, email: string}>
     */
    private const STUDENTS = [
        ['name' => 'Avery Chen', 'email' => 'avery.chen@example.com'],
        ['name' => 'Jordan Blake', 'email' => 'jordan.blake@example.com'],
        ['name' => 'Morgan Reyes', 'email' => 'morgan.reyes@example.com'],
        ['name' => 'Casey Nguyen', 'email' => 'casey.nguyen@example.com'],
        ['name' => 'Riley Patel', 'email' => 'riley.patel@example.com'],
        ['name' => 'Quinn Morales', 'email' => 'quinn.morales@example.com'],
        ['name' => 'Harper Singh', 'email' => 'harper.singh@example.com'],
        ['name' => 'Reese Ortiz', 'email' => 'reese.ortiz@example.com'],
        ['name' => 'Cameron Kim', 'email' => 'cameron.kim@example.com'],
        ['name' => 'Taylor Brooks', 'email' => 'taylor.brooks@example.com'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! DB::table('roles')->where('id', 1)->exists()) {
            throw new LogicException('Role ID 1 must exist before student class users are seeded.');
        }

        $sectionId = DB::table('sections')
            ->where('status', SectionStatusId::Active->value)
            ->value('id');

        if ($sectionId === null) {
            throw new LogicException('An active section must exist before student class records are seeded.');
        }

        DB::transaction(function () use ($sectionId): void {
            $timestamp = now();

            foreach (self::STUDENTS as $student) {
                $user = User::query()->updateOrCreate(
                    ['email' => $student['email']],
                    [
                        'name' => $student['name'],
                        'password' => 'password',
                        'email_verified_at' => $timestamp,
                    ],
                );

                DB::table('user_roles')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'role_id' => 1,
                    ],
                    [
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ],
                );

                DB::table('student_class')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'section_id' => $sectionId,
                    ],
                    [
                        'status' => StudentClassStatus::Approved->value,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ],
                );
            }
        });
    }
}
