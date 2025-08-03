<?php

namespace App\UseCases\Member\Auth;

use App\Models\EmailVerification;
use App\Enums\Common\EmailVerificationStatusEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 仮登録トークンを検証し、有効な場合はメールアドレスを返却する
 *
 * @param string $token
 * @param string $email
 * @throws ModelNotFoundException
 * @return string
 */
final class ValidateRegisterEmailTokenAction
{
    public function __invoke(string $token, string $email): string
    {
        $verification = EmailVerification::where('token', $token)
            ->where('email', $email)
            ->where('status', EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value)
            ->where('expiration_datetime', '>', Carbon::now())
            ->firstOrFail();

        return $verification->email;
    }
}
