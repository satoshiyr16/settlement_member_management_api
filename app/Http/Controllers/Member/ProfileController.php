<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use App\Enums\EmailVerificationStatusEnum;
use App\Http\Requests\Member\EmailAndTokenRequest;
use App\Http\Requests\Member\Profile\UpdateEmailRequest;
use App\Http\Requests\Member\Profile\UpdatePasswordRequest;
use App\Http\Requests\Member\Profile\UpdateProfileRequest;
use App\UseCases\Member\Domain\CreateEmailVerificationReturnTokenAction;
use App\UseCases\Member\Domain\ValidateEmailTokenAction;
use App\UseCases\Member\Profile\SendUpdateUrlEmailAction;
use App\UseCases\Member\Profile\UpdateEmailAction;
use App\UseCases\Member\Profile\UpdatePasswordAction;
use App\UseCases\Member\Profile\UpdateProfileAction;
use App\Http\Resources\Entity\MemberResource;

class ProfileController extends Controller
{
    /**
     * メールアドレス変更
     *
     * @param UpdateEmailRequest $request
     * @param CreateEmailVerificationReturnTokenAction $createVerificationAction
     * @param SendUpdateUrlEmailAction $sendEmailAction
     * @return JsonResponse
     */
    public function sendUpdateEmailToken(
        UpdateEmailRequest $request,
        CreateEmailVerificationReturnTokenAction $createVerificationAction,
        SendUpdateUrlEmailAction $sendEmailAction
    ): JsonResponse
    {
        $validated = $request->validated();
        $token = $createVerificationAction($validated['new_email'], EmailVerificationStatusEnum::SEND_MAIL_UPDATE_EMAIL);
        $sendEmailAction($validated['current_email'], $validated['new_email'], $token);

        return response()->json([], Response::HTTP_CREATED);
    }

    /**
     * メールアドレス変更のトークンを検証する
     *
     * @param EmailAndTokenRequest $request
     * @param ValidateEmailTokenAction $action
     * @return JsonResponse
     */
    public function updateEmail(
        EmailAndTokenRequest $request,
        ValidateEmailTokenAction $validateAction,
        UpdateEmailAction $updateAction
    ): JsonResponse
    {
        $validated = $request->validated();
        $email = $validateAction($validated['token'], $validated['email'], EmailVerificationStatusEnum::SEND_MAIL_UPDATE_EMAIL);
        $updateAction($validated['token'], $email);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * パスワード変更
     *
     * @param UpdatePasswordRequest $request
     * @param UpdatePasswordAction $updateAction
     * @return JsonResponse
     */
    public function updatePassword(
        UpdatePasswordRequest $request,
        UpdatePasswordAction $updateAction
    ): JsonResponse
    {
        $validated = $request->validated();
        $updateAction($validated['current_password'], $validated['new_password']);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * メンバー情報更新
     *
     * @param UpdateProfileRequest $request
     * @param UpdateProfileAction $action
     * @return JsonResponse
     */
    public function updateProfile(
        UpdateProfileRequest $request,
        UpdateProfileAction $action
    ): JsonResponse
    {
        $validated = $request->validated();
        $memberProfile = $action($validated);

        return response()->json(MemberResource::make($memberProfile), Response::HTTP_OK);
    }
}
