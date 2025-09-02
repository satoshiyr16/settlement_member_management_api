<?php

namespace App\UseCases\Member\Auth;

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Member\Auth\LoginMemberRequest;

class LoginMemberAction
{
    /**
     * ユーザーログインを実行する
     *
     * @param LoginMemberRequest $request
     * @param array<string, mixed> $values
     * @return void
     * @throws ValidationException
     */
    public function __invoke(LoginMemberRequest $request, array $values): void
    {
        $user = User::where('email', $values['email'])
            ->where('role', UserRoleEnum::MEMBER->value)
            ->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        $credentials = [
            'email' => $user->email,
            'password' => $values['password'],
            'role' => UserRoleEnum::MEMBER->value,
        ];

        if (!Auth::guard('member')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        $request->session()->regenerate();
    }
}
