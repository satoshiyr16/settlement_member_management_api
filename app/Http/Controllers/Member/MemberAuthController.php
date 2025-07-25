<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\Auth\MemberProvRegisterRequest;
use App\Http\Requests\Member\Auth\MemberRegisterRequest;
use App\UseCases\Member\CreateMemberAction;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class MemberAuthController extends Controller
{

    public function provisionalRegister(
        MemberProvRegisterRequest $request,
        CreateMemberAction $action
    ): JsonResponse
    {
        $validated = $request->validated();
        $action($validated);

        return response()->json([], Response::HTTP_CREATED);
    }
    /**
     * 新規登録
     */
    public function register(MemberRegisterRequest $request, CreateMemberAction $action): JsonResponse
    {
        $validated = $request->validated();
        $action($validated);

        return response()->json([], Response::HTTP_CREATED);
    }
}
