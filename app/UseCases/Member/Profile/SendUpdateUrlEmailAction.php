<?php

namespace App\UseCases\Member\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\Member\UpdateEmail;
use App\Enums\UserRoleEnum;
use Illuminate\Validation\ValidationException;

/**
 * メンバーのメールアドレス変更のURLを付与したメールを送信する
 *
 * @param string $email
 * @param string $token
 * @return void
 */
final class SendUpdateUrlEmailAction
{
    public function __invoke(string $currentEmail, string $newEmail, string $token): void
    {
        $user = Auth::guard('member')->user();
        if ($user->email !== $currentEmail || $user->role !== UserRoleEnum::MEMBER->value) {
            throw ValidationException::withMessages([
                'current_email' => ['メールアドレスが一致しません。']
            ]);
        }

        $nickname = $user->memberProfile->nickname;
        $mail = new UpdateEmail($newEmail, $token, $nickname);
        Mail::to($newEmail)->send($mail);
    }
}
