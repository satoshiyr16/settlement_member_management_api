<?php

namespace App\UseCases\Member\Auth;

use App\Models\User;
use App\Enums\Common\UserRoleEnum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class LoginMemberAction
{
    /**
     * ユーザーログインを実行する
     *
     * @param array<string, mixed> $data
     * @param UserRoleEnum $role
     * @param string $guard
     * @return void
     * @throws ValidationException
     */
    public function __invoke(array $values): void
    {
        $user = User::where('email', $values['email'])
            ->where('role', UserRoleEnum::MEMBER->value)
            ->first();

        if (!$user || !Hash::check($values['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }
        $credentials = [
            'email' => $user->email,
            'password' => $values['password'],
            'role' => UserRoleEnum::MEMBER->value,
        ];

        Session::start();

        if (!auth('member')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        Session::regenerate();
        Session::save();
    }
}
