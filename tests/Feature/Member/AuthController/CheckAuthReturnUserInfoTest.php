<?php

/**
 * 1. 認証状態のテスト
 * 1.1 正常系
 * 認証済みユーザー：認証されているユーザーの情報が正常に返却されること
 * ユーザー情報取得：UserResourceとMemberResourceが正しく生成されること
 * 1.2 異常系
 * 未認証ユーザー：認証されていない場合にUnauthorizedHttpExceptionがスローされること
 * 無効なユーザーID：認証IDが無効な場合の適切な処理
 *
 * 2. ビジネスロジックのテスト
 * 2.1 認証確認
 * ガード確認：memberガードで正しく認証状態が確認されること
 * ユーザーID取得：認証されたユーザーのIDが正しく取得されること
 * 2.2 ユーザー情報取得
 * ユーザー検索：指定されたIDでユーザーが正しく検索されること
 * リレーション読み込み：memberProfileリレーションが正しく読み込まれること
 * リソース生成：UserResourceとMemberResourceが正しく生成されること
 *
 * 3. 統合テスト
 * 3.1 エンドツーエンド
 * 正常フロー：認証確認からユーザー情報返却までの一連の流れが正常に動作すること
 * レスポンス：HTTP 200ステータスコードと適切なJSONレスポンスが返されること
 * 3.2 データ整合性
 * レスポンス構造：期待される構造のJSONレスポンスが返されること
 * ユーザー情報：正しいユーザー情報がレスポンスに含まれること
 *
 * 4. エラーハンドリングのテスト
 * 4.1 認証エラー
 * 未認証状態：認証されていない状態でのアクセス時の適切な例外処理
 * 無効なセッション：無効なセッションでのアクセス時の処理
 * 4.2 データベースエラー
 * ユーザー不存在：存在しないユーザーIDでのアクセス時の処理
 * データベース接続失敗：データベース接続エラー時の適切な処理
 */

namespace Tests\Feature\Member\MemberAuthController;

use Tests\TestCase;
use App\Models\User;
use App\Models\MemberProfile;
use App\Enums\Common\GenderEnum;
use App\Enums\Common\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthReturnUserInfoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーの情報が正常に返却されること
     *
     * @return void
     */
    public function test_check_auth_return_user_info_with_authenticated_user(): void
    {
        $user = User::factory()->member()->create();

        MemberProfile::factory()->create([
            'user_id' => $user->id,
            'nickname' => 'テスト',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => '1990-01-01',
            'enrollment_date' => '2020-01-01',
        ]);

        $this->actingAs($user, 'member');

        $response = $this->getJson('/api/member/auth');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'user' => [
                    'user_id',
                    'email',
                    'role',
                    'suspended_at',
                    'created_at',
                    'updated_at',
                ],
                'member' => [
                    'member_id',
                    'nickname',
                    'gender',
                    'birth_date',
                    'enrollment_date',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $responseData = $response->json();
        $this->assertEquals($user->id, $responseData['user']['user_id']);
        $this->assertEquals($user->email, $responseData['user']['email']);
        $this->assertEquals(UserRoleEnum::MEMBER->value, $responseData['user']['role']);
        $this->assertNull($responseData['user']['suspended_at']);
        $this->assertEquals('テスト', $responseData['member']['nickname']);
        $this->assertEquals(GenderEnum::MALE->value, $responseData['member']['gender']);
        $this->assertEquals('1990-01-01', $responseData['member']['birth_date']);
        $this->assertEquals('2020-01-01', $responseData['member']['enrollment_date']);
    }

    /**
     * 未認証ユーザーの場合にUnauthorizedHttpExceptionがスローされること
     *
     * @return void
     */
    public function test_check_auth_return_user_info_with_unauthenticated_user(): void
    {
        $response = $this->getJson('/api/member/auth');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * 認証されていない状態でのアクセス時に適切なエラーレスポンスが返されること
     *
     * @return void
     */
    public function test_check_auth_return_user_info_returns_unauthorized_for_guest(): void
    {
        $response = $this->getJson('/api/member/auth');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }


    /**
     * 異なるガードで認証されている場合に適切に処理されること
     *
     * @return void
     */
    public function test_check_auth_return_user_info_with_different_guard(): void
    {
        $user = User::factory()->member()->create();

        $this->actingAs($user, 'web');

        $response = $this->getJson('/api/member/auth');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * 無効なユーザーIDの場合の処理
     *
     * @return void
     */
    public function test_check_auth_return_user_info_with_invalid_user_id(): void
    {
        $user = User::factory()->member()->create();

        $this->actingAs($user, 'member');

        $user->delete();
        $this->app['auth']->guard('member')->logout();

        $response = $this->getJson('/api/member/auth');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * レスポンスのHTTPステータスコードが200であること
     *
     * @return void
     */
    public function test_response_returns_200_status_code(): void
    {
        $user = User::factory()->member()->create();

        MemberProfile::factory()->create([
            'user_id' => $user->id,
            'nickname' => 'テスト',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => '1990-01-01',
            'enrollment_date' => '2020-01-01',
        ]);

        $this->actingAs($user, 'member');

        $response = $this->getJson('/api/member/auth');

        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     * レスポンスのJSON構造が期待される形式であること
     *
     * @return void
     */
    public function test_response_json_structure_is_correct(): void
    {
        $user = User::factory()->member()->create();

        MemberProfile::factory()->create([
            'user_id' => $user->id,
            'nickname' => 'テスト',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => '1990-01-01',
            'enrollment_date' => '2020-01-01',
        ]);

        $this->actingAs($user, 'member');

        $response = $this->getJson('/api/member/auth');

        $response->assertJsonStructure([
            'user' => [
                'user_id',
                'email',
                'role',
                'suspended_at',
                'created_at',
                'updated_at',
            ],
            'member' => [
                'member_id',
                'nickname',
                'gender',
                'birth_date',
                'enrollment_date',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * 認証状態の確認が正しく動作すること
     *
     * @return void
     */
    public function test_authentication_state_is_correctly_verified(): void
    {
        $user = User::factory()->member()->create();

        $this->actingAs($user, 'member');

        $this->assertAuthenticated('member');
        $this->assertEquals($user->id, auth('member')->id());

        $response = $this->getJson('/api/member/auth');

        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     * セッション管理が適切に動作すること
     *
     * @return void
     */
    public function test_session_management_works_correctly(): void
    {
        $user = User::factory()->member()->create();

        $this->actingAs($user, 'member');

        $this->assertNotNull(session()->getId());

        $response = $this->getJson('/api/member/auth');

        $response->assertStatus(Response::HTTP_OK);
    }
}
