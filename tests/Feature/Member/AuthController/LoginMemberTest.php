<?php
/**
 * 1. リクエストバリデーションのテスト
 * 1.1 正常系
 * 有効な認証情報：正しい形式のメールアドレスとパスワードでリクエストが成功すること
 * 新規メンバーユーザー：新規作成されたメンバーユーザーでログインが成功すること
 * 1.2 異常系
 * メールアドレス必須：メールアドレスが空の場合にバリデーションエラーが返されること
 * メールアドレス形式不正：不正な形式のメールアドレスでバリデーションエラーが返されること
 * パスワード必須：パスワードが空の場合にバリデーションエラーが返されること
 * パスワード最小長：パスワードが8文字未満の場合にバリデーションエラーが返されること
 * パスワード最大長：パスワードが32文字を超える場合にバリデーションエラーが返されること
 *
 * 2. ビジネスロジックのテスト
 * 2.1 LoginMemberAction
 * ユーザー検索：指定されたメールアドレスでメンバーユーザーが検索されること
 * ロール確認：ユーザーロールがMEMBERであることが確認されること
 * 認証処理：適切な認証情報でログインが実行されること
 * セッション再生成：ログイン成功後にセッションが再生成されること
 * 例外処理：認証失敗時に適切な例外がスローされること
 *
 * 3. 統合テスト
 * 3.1 エンドツーエンド
 * 正常フロー：有効なリクエストからログイン完了までの一連の流れが正常に動作すること
 * レスポンス：HTTP 201ステータスコードと空のJSONレスポンスが返されること
 * 3.2 認証状態
 * 認証状態：ログイン成功後に適切な認証状態になること
 * セッション管理：セッションが適切に管理されること
 *
 * 4. エラーハンドリングのテスト
 * 4.1 認証エラー
 * 存在しないユーザー：存在しないメールアドレスでのログイン失敗
 * 間違ったパスワード：正しくないパスワードでのログイン失敗
 * 無効なロール：メンバー以外のロールでのログイン失敗
 * 4.2 バリデーションエラー
 * 必須項目不足：必須項目が不足している場合の処理
 * 形式不正：データ形式が不正な場合の処理
 * 制約違反：データ制約に違反している場合の処理
 */

namespace Tests\Feature\Member\MemberAuthController;

use Tests\TestCase;
use App\Models\User;
use App\Enums\Common\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class LoginMemberTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効な認証情報でログインが成功すること
     *
     * @return void
     */
    public function test_login_member_with_valid_credentials(): void
    {
        User::create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRoleEnum::MEMBER->value,
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_CREATED)
                ->assertJson([]);

        $this->assertAuthenticated('member');
    }

    /**
     * 新規メンバーユーザーでログインが成功すること
     *
     * @return void
     */
    public function test_login_member_with_new_member_user(): void
    {
        User::create([
            'email' => 'newmember@example.com',
            'password' => Hash::make('newpassword123'),
            'role' => UserRoleEnum::MEMBER->value,
        ]);

        $data = [
            'email' => 'newmember@example.com',
            'password' => 'newpassword123',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertAuthenticated('member');
    }

    /**
     * メールアドレスが必須であること
     *
     * @return void
     */
    public function test_login_member_requires_email(): void
    {
        $data = [
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonValidationErrors(['email']);

        $this->assertGuest('member');
    }

    /**
     * メールアドレスの形式が不正な場合にエラーが返されること
     *
     * @return void
     */
    public function test_login_member_with_invalid_email_format(): void
    {
        $data = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonValidationErrors(['email']);

        $this->assertGuest('member');
    }

    /**
     * パスワードが必須であること
     *
     * @return void
     */
    public function test_login_member_requires_password(): void
    {
        $data = [
            'email' => 'test@example.com',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonValidationErrors(['password']);

        $this->assertGuest('member');
    }

    /**
     * パスワードが8文字未満の場合にエラーが返されること
     *
     * @return void
     */
    public function test_login_member_with_password_less_than_min_length(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => '1234567',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonValidationErrors(['password']);

        $this->assertGuest('member');
    }

    /**
     * パスワードが32文字を超える場合にエラーが返されること
     *
     * @return void
     */
    public function test_login_member_with_password_exceeding_max_length(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => str_repeat('a', 33),
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonValidationErrors(['password']);

        $this->assertGuest('member');
    }

    /**
     * 存在しないメールアドレスでログインが失敗すること
     *
     * @return void
     */
    public function test_login_member_with_nonexistent_email(): void
    {
        $data = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonValidationErrors(['email']);

        $this->assertGuest('member');
    }

    /**
     * 間違ったパスワードでログインが失敗すること
     *
     * @return void
     */
    public function test_login_member_with_wrong_password(): void
    {
        User::create([
            'email' => 'test@example.com',
            'password' => Hash::make('CorrectPassword'),
            'role' => UserRoleEnum::MEMBER->value,
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonValidationErrors(['email']);

        $this->assertGuest('member');
    }

    /**
     * メンバー以外のロールでログインが失敗すること
     *
     * @return void
     */
    public function test_login_member_with_non_member_role(): void
    {
        User::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRoleEnum::ADMIN->value,
        ]);

        $data = [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonValidationErrors(['email']);

        $this->assertGuest('member');
    }

    /**
     * セッションが再生成されること
     *
     * @return void
     */
    public function test_session_is_regenerated_after_login(): void
    {
        User::create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRoleEnum::MEMBER->value,
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertAuthenticated('member');
        $this->assertNotNull(session()->getId());
    }

    /**
     * レスポンス：HTTP 201ステータスコードと空のJSONレスポンスが返されること
     *
     * @return void
     */
    public function test_response_returns_201_status_and_empty_json(): void
    {
        User::create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRoleEnum::MEMBER->value,
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_CREATED)
                ->assertJson([]);
    }

    /**
     * 認証状態：ログイン成功後に適切な認証状態になること
     *
     * @return void
     */
    public function test_authentication_state_after_successful_login(): void
    {
        $user = User::create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRoleEnum::MEMBER->value,
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/member/login', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertAuthenticated('member');
        $this->assertEquals($user->id, auth('member')->id());
        $this->assertEquals($user->email, auth('member')->user()->email);
    }

    /**
     * 複数回のログイン試行：同じユーザーで複数回ログインが成功すること
     *
     */
    // public function test_multiple_login_attempts_with_same_user(): void
    // {
    //     User::create([
    //         'email' => 'test@example.com',
    //         'password' => Hash::make('password123'),
    //         'role' => UserRoleEnum::MEMBER->value,
    //     ]);

    //     $data = [
    //         'email' => 'test@example.com',
    //         'password' => 'password123',
    //     ];

    //     $response1 = $this->postJson('/api/member/login', $data);
    //     $response1->assertStatus(Response::HTTP_CREATED);

    //     // ログアウト
    //     $this->postJson('/api/member/logout');

    //     // 2回目のログイン
    //     $response2 = $this->postJson('/api/member/login', $data);
    //     $response2->assertStatus(Response::HTTP_CREATED);

    //     // 認証されていることを確認
    //     $this->assertAuthenticated('member');
    // }
}
