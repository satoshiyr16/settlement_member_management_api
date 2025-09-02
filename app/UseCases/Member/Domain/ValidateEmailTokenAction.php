<?php

namespace App\UseCases\Member\Domain;

use App\Models\EmailVerification;
use App\Enums\EmailVerificationStatusEnum;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * 仮登録トークンを検証し、有効な場合はメールアドレスを返却する
 *
 * @param string $token
 * @param string $email
 * @param EmailVerificationStatusEnum $statusEnum
 * @throws ModelNotFoundException
 * @throws UnprocessableEntityHttpException
 * @return string
 */
final class ValidateEmailTokenAction
{
    public function __invoke(string $token, string $email, EmailVerificationStatusEnum $statusEnum): string
    {
        $status = $statusEnum->value;
        $verification = EmailVerification::where('token', $token)
            ->where('email', $email)
            ->firstOrFail();

        if ($verification->expiration_datetime <= Carbon::now()) {
            throw new UnprocessableEntityHttpException('トークンの有効期限が切れています。');
        }

        if (
            $statusEnum === EmailVerificationStatusEnum::SEND_MAIL_REGISTER &&
            $verification->status === EmailVerificationStatusEnum::COMPLETED_REGISTER->value
        ) {
            throw new UnprocessableEntityHttpException('すでに変更されています。');
        }

        if (
            $statusEnum === EmailVerificationStatusEnum::SEND_MAIL_UPDATE_EMAIL &&
            $verification->status === EmailVerificationStatusEnum::COMPLETED_UPDATE_EMAIL->value
        ) {
            throw new UnprocessableEntityHttpException('すでに変更されています。');
        }

        if ($verification->status !== $status) {
            throw new ModelNotFoundException('無効なステータスです。');
        }

        return $verification->email;
    }
}
