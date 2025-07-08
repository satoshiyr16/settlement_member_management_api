<?php

namespace App\UseCases\Member;

use App\Models\User;
use App\Models\MemberProfile;
use App\Enums\Common\GenderEnum;
use App\Enums\Common\UserRoleEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * メンバーの新規登録
 *
 * @param array $values
 * @return void
 */
final class CreateMemberAction
{
    public function __invoke(array $values): void
    {
        DB::transaction(function () use ($values) {
            $user = User::create([
                'email' => $values['email'],
                'password' => Hash::make($values['password']),
                'role' => UserRoleEnum::MEMBER->value,
            ]);

            MemberProfile::create([
                'user_id' => $user->id,
                'nickname' => $values['nickname'],
                'gender' => $values['gender'],
                'birth_date' => $values['birth_date'],
                'enrollment_date' => $values['enrollment_date'],
            ]);
        });
    }
}
