<?php

namespace App\UseCases\Member\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Enums\UserRoleEnum;
use App\Enums\EmailVerificationStatusEnum;
use App\Models\User;
use App\Models\EmailVerification;

/**
 * メンバーのメールアドレス変更を行う
 *
 * @param string $token
 * @param string $email
 * @return void
 */
final class UpdateEmailAction
{
    public function __invoke(string $token, string $email): void
    {
        $userId = Auth::guard('member')->user()->id;
        $user = User::where('role', UserRoleEnum::MEMBER->value)->find($userId);
        $verification = EmailVerification::where('token', $token)
            ->where('email', $email)
            ->firstOrFail();

        if ($verification->status === EmailVerificationStatusEnum::COMPLETED_UPDATE_EMAIL->value) {
            throw new UnprocessableEntityHttpException('すでに変更されています。');
        }

        DB::transaction(function () use ($email, $user, $verification) {
            $user->email = $email;
            $user->save();

            $verification->status = EmailVerificationStatusEnum::COMPLETED_UPDATE_EMAIL->value;
            $verification->save();
        });
    }
}
