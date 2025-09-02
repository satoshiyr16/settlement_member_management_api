<?php

namespace App\UseCases\Member\Domain;


use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Enums\EmailVerificationStatusEnum;
use App\Models\EmailVerification;

/**
 * メンバーの仮登録時のメールとtokenをdbに保存し、tokenを返却する
 *
 * @param string $email
 * @return string $token
 */
final class CreateEmailVerificationReturnTokenAction
{
    public function __invoke(string $email, EmailVerificationStatusEnum $statusEnum): string
    {
        $token = Str::random(250);
        $status = $statusEnum->value;
        /** tokenの有効期限は1時間に固定 */
        $expirationDatetime = Carbon::now()->addHour();

        DB::transaction(function () use ($email, $token, $status, $expirationDatetime) {
            EmailVerification::create([
                'email' => $email,
                'token' => $token,
                'status' => $status,
                'expiration_datetime' => $expirationDatetime,
            ]);
        });

        return $token;
    }
}
