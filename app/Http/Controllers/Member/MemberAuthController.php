<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Member\Auth\MemberRegisterRequest;

class MemberAuthController extends Controller
{
    /**
     * 新規登録
     */
    public function register(MemberRegisterRequest $request)
    {
        $validated = $request->validated();
    }
}
