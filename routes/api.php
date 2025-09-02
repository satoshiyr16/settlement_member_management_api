<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\Member\AuthController;
use App\Http\Controllers\Member\ProfileController;

Route::get('/init', function () {
    return response()->json([], Response::HTTP_OK);
});

Route::get('/master-data', [MasterDataController::class, 'getAllMasterData']);

Route::prefix('member')->group(function () {
    Route::get('/validate-email-token', [AuthController::class, 'validateRegisterEmailToken']);
    Route::post('/provisional-register', [AuthController::class, 'provisionalCreateMember']);
    Route::post('/register', [AuthController::class, 'createMember']);
    Route::post('/login', [AuthController::class, 'loginMember']);

    Route::middleware('auth:member')->group(function () {
        Route::get('/auth', [AuthController::class, 'checkAuthReturnUserInfo']);
        Route::prefix('profile')->group(function () {
            Route::post('/token', [ProfileController::class, 'sendUpdateEmailToken']);
            Route::patch('/mail', [ProfileController::class, 'updateEmail']);
            Route::patch('/password', [ProfileController::class, 'updatePassword']);
            Route::put('/', [ProfileController::class, 'updateProfile']);
        });
    });
});
