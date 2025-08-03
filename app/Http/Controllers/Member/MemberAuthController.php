<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Member\Auth\ProvRegisterRequest;
use App\Http\Requests\Member\Auth\ProvRegisterTokenRequest;
use App\Http\Requests\Member\Auth\RegisterRequest;
use App\Http\Requests\Member\Auth\LoginMemberRequest;
use App\UseCases\Member\Auth\CreateEmailVerificationReturnTokenAction;
use App\UseCases\Member\Auth\SendRegisterUrlEmailAction;
use App\UseCases\Member\Auth\ValidateRegisterEmailTokenAction;
use App\UseCases\Member\Auth\CreateMemberAction;
use App\UseCases\Member\Auth\LoginMemberAction;
use Illuminate\Support\Facades\Log;

class MemberAuthController extends Controller
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
    ): JsonResponse {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $createVerificationAction, $sendEmailAction) {
            $token = $createVerificationAction($validated['email']);
            $sendEmailAction($validated['email'], $token);
        });

        return response()->json([], Response::HTTP_CREATED);
    }

    /**
     * 仮登録メールアドレスのトークンを検証する
     *
     * @param ProvRegisterTokenRequest $request
     * @param ValidateRegisterEmailTokenAction $action
     * @return JsonResponse
     */
    public function validateRegisterEmailToken(
        ProvRegisterTokenRequest $request,
        ValidateRegisterEmailTokenAction $action
    ): JsonResponse {
        $validated = $request->validated();
        $email = $action($validated['token'], $validated['email']);

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
        $action($validated);

        Log::info('Response cookies', [
            'cookies' => $request->headers->get('cookie')
        ]);

        return response()->json([], Response::HTTP_CREATED);
    }
}
