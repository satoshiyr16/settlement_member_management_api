<?php

namespace App\UseCases\Member\Auth;


use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Enums\Common\EmailVerificationStatusEnum;
use App\Models\EmailVerification;

/**
 * メンバーの仮登録時のメールとtokenをdbに保存し、tokenを返却する
 *
 * @param string $email
 * @return string $token
 */
final class CreateEmailVerificationReturnTokenAction
{
    public function __invoke(string $email): string
    {
        $token = Str::random(250);
        $status = EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value;

        /**
         * MEMO： tokenの有効期限は1時間に固定している。
         */
        $expirationDatetime = Carbon::now()->addHour();

        EmailVerification::create([
            'email' => $email,
            'token' => $token,
            'status' => $status,
            'expiration_datetime' => $expirationDatetime,
        ]);

        return $token;
    }
}
