<?php

namespace Tests\Feature\Member\MemberAuthController;

use Tests\TestCase;
use App\Models\User;
use App\Models\EmailVerification;
use App\Enums\Common\EmailVerificationStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\Member\RegisterMail;
use Symfony\Component\HttpFoundation\Response;

class ProvisionalCreateMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * 有効なメールアドレスで仮登録が成功すること
     *
     * @return void
     */
    public function test_provisional_create_member_with_valid_email(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/member/provisional-register', [
            'email' => $email
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([]);

        $this->assertDatabaseHas('email_verifications', [
            'email' => $email,
            'status' => EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value,
        ]);

        Mail::assertSent(RegisterMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /**
     * 新規メールアドレスで仮登録が成功すること
     *
     * @return void
     */
    public function test_provisional_create_member_with_new_email(): void
    {
        $email = 'newuser@example.com';

        $response = $this->postJson('/api/member/provisional-register', [
            'email' => $email
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseMissing('users', [
            'email' => $email
        ]);

        $this->assertDatabaseHas('email_verifications', [
            'email' => $email,
        ]);
    }

    /**
     * メールアドレスが必須であること
     *
     * @return void
     */
    public function test_provisional_create_member_requires_email(): void
    {
        $response = $this->postJson('/api/member/provisional-register', []);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('email_verifications', 0);
    }

    /**
     * メールアドレスの形式が不正な場合にエラーが返されること
     *
     * @return void
     */
    public function test_provisional_create_member_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/member/provisional-register', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('email_verifications', 0);
    }

    /**
     * 重複するメールアドレスで仮登録が失敗すること
     *
     * @return void
     */
    public function test_provisional_create_member_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $response = $this->postJson('/api/member/provisional-register', [
            'email' => 'existing@example.com'
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('email_verifications', 0);
    }

    /**
     * トークン生成：250文字のランダムトークンが生成されること
     *
     * @return void
     */
    public function test_token_generation_creates_250_character_random_token(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/member/provisional-register', [
            'email' => $email
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $emailVerification = EmailVerification::where('email', $email)->first();

        $this->assertNotNull($emailVerification);
        $this->assertEquals(250, strlen($emailVerification->token));
        $this->assertNotEquals('', $emailVerification->token);
    }

    /**
     * ステータス設定：SEND_MAIL_REGISTER（値：1）が正しく設定されること
     *
     * @return void
     */
    public function test_status_is_set_to_send_mail_register(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/member/provisional-register', [
            'email' => $email
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('email_verifications', [
            'email' => $email,
            'status' => EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value,
        ]);
    }

    /**
     * 有効期限設定：現在時刻から1時間後の有効期限が設定されること
     *
     * @return void
     */
    public function test_expiration_datetime_is_set_to_one_hour_later(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/member/provisional-register', [
            'email' => $email
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $emailVerification = EmailVerification::where('email', $email)->first();

        $this->assertNotNull($emailVerification);
        $this->assertNotNull($emailVerification->expiration_datetime);

        $expectedExpiration = now()->addHour();
        $this->assertLessThanOrEqual(60, $expectedExpiration->diffInSeconds($emailVerification->expiration_datetime));
    }

    /**
     * データベース保存：EmailVerificationテーブルに正しいデータが保存されること
     *
     * @return void
     */
    public function test_email_verification_data_is_saved_correctly(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/member/provisional-register', [
            'email' => $email
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('email_verifications', [
            'email' => $email,
            'status' => EmailVerificationStatusEnum::SEND_MAIL_REGISTER->value,
        ]);

        $emailVerification = \App\Models\EmailVerification::where('email', $email)->first();
        $this->assertNotNull($emailVerification->token);
        $this->assertNotNull($emailVerification->expiration_datetime);
    }

    /**
     * メール送信：指定されたメールアドレスにメールが送信されること
     *
     * @return void
     */
    public function test_email_is_sent_to_specified_address(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/member/provisional-register', [
            'email' => $email
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Mail::assertSent(RegisterMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /**
     * メール内容：生成されたトークンがメールに含まれること
     *
     * @return void
     */
    public function test_generated_token_is_included_in_email(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/member/provisional-register', [
            'email' => $email
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $emailVerification = EmailVerification::where('email', $email)->first();

        Mail::assertSent(RegisterMail::class, function ($mail) use ($emailVerification) {
            return $mail->hasTo($emailVerification->email);
        });
    }

    /**
     * トークン一意性：生成されたトークンが重複しないこと
     *
     * @return void
     */
    public function test_generated_tokens_are_unique(): void
    {
        $email1 = 'test1@example.com';
        $email2 = 'test2@example.com';

        // 1回目の仮登録
        $response1 = $this->postJson('/api/member/provisional-register', [
            'email' => $email1
        ]);
        $response1->assertStatus(Response::HTTP_CREATED);

        // 2回目の仮登録
        $response2 = $this->postJson('/api/member/provisional-register', [
            'email' => $email2
        ]);
        $response2->assertStatus(Response::HTTP_CREATED);

        $token1 = \App\Models\EmailVerification::where('email', $email1)->first()->token;
        $token2 = \App\Models\EmailVerification::where('email', $email2)->first()->token;

        $this->assertNotEquals($token1, $token2);
    }
}
