<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\MemberAuthController;
use App\Http\Controllers\MasterDataController;

Route::get('/master-data', [MasterDataController::class, 'getAllMasterData']);

Route::prefix('member')->group(function () {
    Route::post('/provisional-register', [MemberAuthController::class, 'provisionalRegister']);
    Route::post('/register', [MemberAuthController::class, 'register']);
});
