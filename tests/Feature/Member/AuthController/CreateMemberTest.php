<?php

namespace Tests\Feature\Member\MemberAuthController;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemberProfile;
use App\Enums\Common\UserRoleEnum;
use App\Enums\Common\GenderEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class CreateMemberTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効なデータで新規登録が成功すること
     *
     * @return void
     */
    public function test_create_member_with_valid_data(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => '1990-01-01',
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => UserRoleEnum::MEMBER->value,
            'suspended_at' => null,
        ]);

        $user = User::where('email', 'test@example.com')
            ->where('role', UserRoleEnum::MEMBER->value)
            ->first();
        $this->assertDatabaseHas('member_profiles', [
            'user_id' => $user->id,
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => '1990-01-01',
        ]);

        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * 新規メールアドレスで新規登録が成功すること
     *
     * @return void
     */
    public function test_create_member_with_new_email(): void
    {
        $data = [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => '新規ユーザー',
            'gender' => GenderEnum::FEMALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertDatabaseHas('member_profiles', [
            'user_id' => $user->id,
            'nickname' => '新規ユーザー',
            'gender' => GenderEnum::FEMALE->value,
            'birth_date' => null,
        ]);
    }

    /**
     * メールアドレスが必須であること
     *
     * @return void
     */
    public function test_create_member_requires_email(): void
    {
        $data = [
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * メールアドレスの形式が不正な場合にエラーが返されること
     *
     * @return void
     */
    public function test_create_member_with_invalid_email_format(): void
    {
        $data = [
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * 重複するメールアドレスで新規登録が失敗すること
     *
     * @return void
     */
    public function test_create_member_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $data = [
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * パスワードが必須であること
     *
     * @return void
     */
    public function test_create_member_requires_password(): void
    {
        $data = [
            'email' => 'test@example.com',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * パスワードが8文字未満の場合にエラーが返されること
     *
     * @return void
     */
    public function test_create_member_with_password_less_than_min_length(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * パスワードが32文字を超える場合にエラーが返されること
     *
     * @return void
     */
    public function test_create_member_with_password_exceeding_max_length(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => str_repeat('a', 33),
            'password_confirmation' => str_repeat('a', 33),
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * パスワード確認が一致しない場合にエラーが返されること
     *
     * @return void
     */
    public function test_create_member_with_password_confirmation_mismatch(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * ニックネームが必須であること
     *
     * @return void
     */
    public function test_create_member_requires_nickname(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['nickname']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * ニックネームが100文字を超える場合にエラーが返されること
     *
     * @return void
     */
    public function test_create_member_with_nickname_exceeding_max_length(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => str_repeat('a', 101),
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['nickname']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * 性別が必須であること
     *
     * @return void
     */
    public function test_create_member_requires_gender(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['gender']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * 無効な性別値でエラーが返されること
     *
     * @return void
     */
    public function test_create_member_with_invalid_gender_value(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => 999,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['gender']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * 生年月日が今日以降の日付の場合にエラーが返されること
     *
     * @return void
     */
    public function test_create_member_with_future_birth_date(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => now()->addDay()->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonValidationErrors(['birth_date']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('member_profiles', 0);
    }

    /**
     * 生年月日がnullの場合でも新規登録が成功すること
     *
     * @return void
     */
    public function test_create_member_with_null_birth_date(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
            // birth_dateは含めない（null）
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertDatabaseHas('member_profiles', [
            'user_id' => $user->id,
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => null,
        ]);
    }

    /**
     * ユーザーロールが正しく設定されること
     *
     * @return void
     */
    public function test_user_role_is_set_correctly(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => UserRoleEnum::MEMBER->value,
        ]);
    }

    /**
     * 入会日が正しく設定されること
     *
     * @return void
     */
    public function test_enrollment_date_is_set_correctly(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $user = User::where('email', 'test@example.com')->first();
        $memberProfile = MemberProfile::where('user_id', $user->id)->first();

        $this->assertNotNull($memberProfile->enrollment_date);
        $this->assertEquals(now()->format('Y-m-d'), $memberProfile->enrollment_date->format('Y-m-d'));
    }

    /**
     * レスポンス：HTTP 201ステータスコードと空のJSONレスポンスが返されること
     *
     * @return void
     */
    public function test_response_returns_201_status_and_empty_json(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([]);
    }

    /**
     * データベース状態：新規登録完了後、usersテーブルとmember_profilesテーブルの状態が期待通りであること
     *
     * @return void
     */
    public function test_database_state_after_registration(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => '1990-01-01',
        ];

        $response = $this->postJson('/api/member/register', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        /** usersテーブルの確認 */
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => UserRoleEnum::MEMBER->value,
        ]);

        /** member_profilesテーブルの確認 */
        $user = User::where('email', 'test@example.com')->first();
        $this->assertDatabaseHas('member_profiles', [
            'user_id' => $user->id,
            'nickname' => 'テストユーザー',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => '1990-01-01',
        ]);
    }
}

/**
 *
 * 1. リクエストバリデーションのテスト
 * 1.1 正常系
 * 有効なデータ：正しい形式のデータでリクエストが成功すること
 * 新規メールアドレス：既存のユーザーに存在しないメールアドレスでリクエストが成功すること
 * 生年月日null：生年月日がnullの場合でも新規登録が成功すること
 * 1.2 異常系
 * メールアドレス必須：メールアドレスが空の場合にバリデーションエラーが返されること
 * メールアドレス形式不正：不正な形式のメールアドレスでバリデーションエラーが返されること
 * 重複メールアドレス：既存のユーザーに存在するメールアドレスでバリデーションエラーが返されること
 * パスワード必須：パスワードが空の場合にバリデーションエラーが返されること
 * パスワード最小長：パスワードが8文字未満の場合にバリデーションエラーが返されること
 * パスワード最大長：パスワードが32文字を超える場合にバリデーションエラーが返されること
 * パスワード確認不一致：パスワード確認が一致しない場合にバリデーションエラーが返されること
 * ニックネーム必須：ニックネームが空の場合にバリデーションエラーが返されること
 * ニックネーム最大長：ニックネームが100文字を超える場合にバリデーションエラーが返されること
 * 性別必須：性別が空の場合にバリデーションエラーが返されること
 * 性別無効値：無効な性別値でバリデーションエラーが返されること
 * 生年月日未来日付：生年月日が今日以降の日付の場合にバリデーションエラーが返されること
 *
 * 2. ビジネスロジックのテスト
 * 2.1 CreateMemberAction
 * ユーザー作成：Userテーブルに正しいデータが保存されること
 * メンバープロフィール作成：MemberProfileテーブルに正しいデータが保存されること
 * パスワードハッシュ化：パスワードが適切にハッシュ化されること
 * ユーザーロール設定：ユーザーロールがMEMBERに設定されること
 * 入会日設定：入会日が現在時刻に設定されること
 * トランザクション処理：データベース操作が適切にトランザクション内で実行されること
 *
 * 3. 統合テスト
 * 3.1 エンドツーエンド
 * 正常フロー：有効なリクエストから新規登録完了までの一連の流れが正常に動作すること
 * レスポンス：HTTP 201ステータスコードと空のJSONレスポンスが返されること
 * 3.2 データ整合性
 * データベース状態：新規登録完了後、usersテーブルとmember_profilesテーブルの状態が期待通りであること
 * リレーション：UserとMemberProfileの関連が正しく設定されること
 *
 * 4. エラーハンドリングのテスト
 * 4.1 データベースエラー
 * データベース接続失敗：データベース接続エラー時の適切な処理
 * トランザクション失敗：トランザクション内でのエラー時の適切な処理
 * 4.2 バリデーションエラー
 * 必須項目不足：必須項目が不足している場合の処理
 * 形式不正：データ形式が不正な場合の処理
 * 制約違反：データ制約に違反している場合の処理
 */
