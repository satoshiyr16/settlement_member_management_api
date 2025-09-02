<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\User;
use App\Enums\EmailVerificationStatusEnum;
use App\Http\Requests\Member\Auth\ProvRegisterRequest;
use App\Http\Requests\Member\EmailAndTokenRequest;
use App\Http\Requests\Member\Auth\RegisterRequest;
use App\Http\Requests\Member\Auth\LoginMemberRequest;
use App\UseCases\Member\Domain\CreateEmailVerificationReturnTokenAction;
use App\UseCases\Member\Domain\ValidateEmailTokenAction;
use App\UseCases\Member\Auth\SendRegisterUrlEmailAction;
use App\UseCases\Member\Auth\CreateMemberAction;
use App\UseCases\Member\Auth\LoginMemberAction;
use App\Http\Resources\Entity\UserResource;
use App\Http\Resources\Entity\MemberResource;

class AuthController extends Controller
{
    /**
     * 仮登録
     *
     * @param MemberProvRegisterRequest $request
     * @param CreateEmailVerificationReturnTokenAction $createVerificationAction
     * @param SendRegisterUrlEmailAction $sendEmailAction
     * @return JsonResponse
     */
    public function provisionalCreateMember(
        ProvRegisterRequest $request,
        CreateEmailVerificationReturnTokenAction $createVerificationAction,
        SendRegisterUrlEmailAction $sendEmailAction
    ): JsonResponse
    {
        $validated = $request->validated();
        $token = $createVerificationAction($validated['email'], EmailVerificationStatusEnum::SEND_MAIL_REGISTER);
        $sendEmailAction($validated['email'], $token);

        return response()->json([], Response::HTTP_CREATED);
    }

    /**
     * 仮登録メールアドレスのトークンを検証する
     *
     * @param EmailAndTokenRequest $request
     * @param ValidateRegisterEmailTokenAction $action
     * @return JsonResponse
     */
    public function validateRegisterEmailToken(
        EmailAndTokenRequest $request,
        ValidateEmailTokenAction $action
    ): JsonResponse
    {
        $validated = $request->validated();
        $email = $action($validated['token'], $validated['email'], EmailVerificationStatusEnum::SEND_MAIL_REGISTER);

        return response()->json(['email' => $email], Response::HTTP_OK);
    }

    /**
     * 新規登録
     *
     * @param MemberRegisterRequest $request
     * @param CreateMemberAction $action
     * @return JsonResponse
     */
    public function createMember(RegisterRequest $request, CreateMemberAction $action): JsonResponse
    {
        $validated = $request->validated();
        $action($validated);

        return response()->json([], Response::HTTP_CREATED);
    }

    /**
     * ログイン
     *
     * @param LoginMemberRequest $request
     * @param LoginMemberAction $action
     * @return JsonResponse
     */
    public function loginMember(LoginMemberRequest $request, LoginMemberAction $action): JsonResponse
    {
        $validated = $request->validated();
        $action($request, $validated);

        return response()->json([], Response::HTTP_CREATED);
    }

    /**
     * 認証されているか確認後、ログインしているユーザーのデータを返却する
     *
     * @return JsonResponse
     */
    public function checkAuthReturnUserInfo(): JsonResponse
    {
        $userId = Auth::guard('member')->id();
        if (!$userId) {
            throw new UnauthorizedHttpException('Unauthorized');
        }

        $user = User::with('memberProfile')->find($userId);

        return response()->json([
            'user' => UserResource::make($user),
            'member' => MemberResource::make($user->memberProfile)
        ], Response::HTTP_OK);
    }
}
