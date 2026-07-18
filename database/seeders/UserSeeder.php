<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LogicException;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleIds = DB::table('roles')->pluck('id')->all();

        if ($roleIds === []) {
            throw new LogicException('Roles must be seeded before users.');
        }

        if (! in_array(2, $roleIds, true)) {
            throw new LogicException('Role ID 2 must exist before users are seeded.');
        }

        DB::transaction(function () use ($roleIds): void {
            $timestamp = now();
            $userRoles = User::factory()
                ->count(10)
                ->create()
                ->map(fn (User $user, int $index): array => [
                    'user_id' => $user->id,
                    'role_id' => $index === 0 ? 2 : Arr::random($roleIds),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])
                ->all();

            DB::table('user_roles')->insert($userRoles);
        });
    }
}
