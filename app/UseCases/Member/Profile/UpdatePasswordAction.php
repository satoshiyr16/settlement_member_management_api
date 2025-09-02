<?php

namespace App\UseCases\Member\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Enums\UserRoleEnum;
use App\Models\User;

/**
 * メンバーのパスワード変更を行う
 *
 * @param string $currentPassword
 * @param string $newPassword
 * @return void
 */
final class UpdatePasswordAction
{
    public function __invoke(string $currentPassword, string $newPassword): void
    {
        $userId = Auth::guard('member')->user()->id;
        $user = User::where('role', UserRoleEnum::MEMBER->value)->find($userId);

        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['現在のパスワードが一致しません。']
            ]);
        }

        DB::transaction(function () use ($newPassword, $user) {
            $user->password = Hash::make($newPassword);
            $user->save();
        });
    }
}
