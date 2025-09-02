<?php

namespace Database\Seeders\Entity;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MemberProfile;
use App\Enums\UserRoleEnum;
use App\Enums\GenderEnum;

class MemberProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $memberUsers = User::where('role', UserRoleEnum::MEMBER->value)->get();

        foreach ($memberUsers as $user) {
            MemberProfile::create([
                'user_id' => $user->id,
                'nickname' => 'テストユーザー' . $user->id,
                'gender' => GenderEnum::cases()[rand(0, count(GenderEnum::cases()) - 1)],
                'birth_date' => '2000-01-01',
                'enrollment_date' => now(),
            ]);
        }
    }
}
