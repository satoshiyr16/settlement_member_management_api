<?php

namespace Tests\Feature\Member\MemberAuthController;

use Tests\TestCase;
use App\Models\EmailVerification;
use App\Enums\Common\EmailVerificationStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

class ValidateRegisterEmailTokenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効なトークンとメールアドレスで検証が成功すること
     *
     * @return void
     */
    public function test_validate_register_email_token_with_valid_token_and_email(): void
    {
        $email = 'test@example.com';
        $token = 'valid-token-123';

        EmailVerification::create([
            'email' => $email,
            'token' => $token,
            'status' => EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value,
            'expiration_datetime' => now()->addHour(),
        ]);

        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => $token,
            'email' => $email
        ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['email' => $email]);
    }

    /**
     * 新規トークンとメールアドレスで検証が成功すること
     *
     * @return void
     */
    public function test_validate_register_email_token_with_new_token_and_email(): void
    {
        $email = 'newuser@example.com';
        $token = 'new-token-456';

        EmailVerification::create([
            'email' => $email,
            'token' => $token,
            'status' => EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value,
            'expiration_datetime' => now()->addHour(),
        ]);

        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => $token,
            'email' => $email
        ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['email' => $email]);
    }

    /**
     * トークンが必須であること
     *
     * @return void
     */
    public function test_validate_register_email_token_requires_token(): void
    {
        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'email' => 'test@example.com'
        ]));

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['token']);
    }

    /**
     * メールアドレスが必須であること
     *
     * @return void
     */
    public function test_validate_register_email_token_requires_email(): void
    {
        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => 'test-token'
        ]));

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * メールアドレスの形式が不正な場合にエラーが返されること
     *
     * @return void
     */
    public function test_validate_register_email_token_with_invalid_email_format(): void
    {
        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => 'test-token',
            'email' => 'invalid-email'
        ]));

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * トークンが255文字を超える場合にエラーが返されること
     *
     * @return void
     */
    public function test_validate_register_email_token_with_token_exceeding_max_length(): void
    {
        $longToken = str_repeat('a', 256);

        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => $longToken,
            'email' => 'test@example.com'
        ]));

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['token']);
    }

    /**
     * 存在しないトークンで検証が失敗すること
     *
     * @return void
     */
    public function test_validate_register_email_token_with_nonexistent_token(): void
    {
        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => 'nonexistent-token',
            'email' => 'test@example.com'
        ]));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * 存在しないメールアドレスで検証が失敗すること
     *
     * @return void
     */
    public function test_validate_register_email_token_with_nonexistent_email(): void
    {
        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => 'test-token',
            'email' => 'nonexistent@example.com'
        ]));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * トークンとメールアドレスの組み合わせが一致しない場合に検証が失敗すること
     *
     * @return void
     */
    public function test_validate_register_email_token_with_mismatched_token_and_email(): void
    {
        EmailVerification::create([
            'email' => 'user1@example.com',
            'token' => 'token-1',
            'status' => EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value,
            'expiration_datetime' => now()->addHour(),
        ]);

        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => 'token-1',
            'email' => 'user2@example.com'
        ]));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * ステータスがSEND_MAIL_REGISTER以外の場合に検証が失敗すること
     *
     * @return void
     */
    public function test_validate_register_email_token_with_invalid_status(): void
    {
        $email = 'test@example.com';
        $token = 'test-token';

        EmailVerification::create([
            'email' => $email,
            'token' => $token,
            'status' => EmailVerificationStatusEnum::COMPLETED_REGISTER->value,
            'expiration_datetime' => now()->addHour(),
        ]);

        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => $token,
            'email' => $email
        ]));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * 有効期限が切れたトークンで検証が失敗すること
     *
     * @return void
     */
    public function test_validate_register_email_token_with_expired_token(): void
    {
        $email = 'test@example.com';
        $token = 'expired-token';

        EmailVerification::create([
            'email' => $email,
            'token' => $token,
            'status' => EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value,
            'expiration_datetime' => now()->subHour(),
        ]);

        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => $token,
            'email' => $email
        ]));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * レスポンス：HTTP 200ステータスコードとメールアドレスが含まれたJSONレスポンスが返されること
     *
     * @return void
     */
    public function test_response_returns_200_status_and_email_in_json(): void
    {
        $email = 'test@example.com';
        $token = 'test-token';

        EmailVerification::create([
            'email' => $email,
            'token' => $token,
            'status' => EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value,
            'expiration_datetime' => now()->addHour(),
        ]);

        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => $token,
            'email' => $email
        ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['email' => $email]);
    }

    /**
     * データベース状態：検証完了後もEmailVerificationレコードが削除されないこと
     *
     * @return void
     */
    public function test_database_state_after_validation_email_verification_record_remains(): void
    {
        $email = 'test@example.com';
        $token = 'test-token';

        EmailVerification::create([
            'email' => $email,
            'token' => $token,
            'status' => EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value,
            'expiration_datetime' => now()->addHour(),
        ]);

        $response = $this->getJson('/api/member/validate-email-token?' . http_build_query([
            'token' => $token,
            'email' => $email
        ]));

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('email_verifications', [
            'email' => $email,
            'token' => $token,
        ]);
    }
}
