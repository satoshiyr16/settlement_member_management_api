<?php

namespace App\UseCases\Member\Auth;

use Illuminate\Support\Facades\Mail;
use App\Mail\Member\RegisterEmail;

/**
 * メンバー新規登録のURLを付与したメールを送信する
 *
 * @param string $email
 * @param string $token
 * @return void
 */
final class SendRegisterUrlEmailAction
{
    public function __invoke(string $email, string $token): void
    {
        $mail = new RegisterEmail($email, $token);

        try {
            Mail::to($email)->send($mail);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
