<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\MemberAuthController;
use App\Http\Controllers\MasterDataController;
use Symfony\Component\HttpFoundation\Response;

Route::get('/init', function () {
    return response()->json([], Response::HTTP_OK);
});

Route::get('/master-data', [MasterDataController::class, 'getAllMasterData']);

Route::prefix('member')->group(function () {
    Route::get('/validate-email-token', [MemberAuthController::class, 'validateRegisterEmailToken']);
    Route::post('/provisional-register', [MemberAuthController::class, 'provisionalCreateMember']);
    Route::post('/register', [MemberAuthController::class, 'createMember']);
    Route::post('/login', [MemberAuthController::class, 'loginMember']);

    Route::middleware('auth:member')->group(function () {
        Route::get('/auth', [MemberAuthController::class, 'checkAuthReturnUserInfo']);
    });
});
