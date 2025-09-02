<?php

namespace Database\Seeders\Entity;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'role' => UserRoleEnum::ADMIN->value,
        ]);

        /** 3人分のメンバーユーザーを作成 */
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'email' => "member{$i}@example.com",
                'password' => Hash::make('password123'),
                'role' => UserRoleEnum::MEMBER->value,
                'suspended_at' => null,
            ]);
        }
    }
}
