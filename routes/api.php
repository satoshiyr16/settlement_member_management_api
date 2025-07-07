<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\MemberAuthController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
        'timestamp' => now()->toISOString()
    ]);
});

Route::prefix('member')->group(function () {
    Route::post('/register', [MemberAuthController::class, 'register']);
});
