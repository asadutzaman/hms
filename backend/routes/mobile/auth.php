<?php

use App\Http\Controllers\Api\V1\Mobile\Auth\MobileAuthController;
use Illuminate\Support\Facades\Route;

/*
| Staff authentication for the mobile app  —  prefix /api/v1/mobile/auth
| (Patient authentication lives in routes/mobile/patient.php.)
*/

Route::prefix('auth')->middleware(['restrictIp'])->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login']);
    Route::post('/refresh', [MobileAuthController::class, 'refresh']);

    Route::middleware(['authVerify'])->group(function () {
        Route::post('/logout', [MobileAuthController::class, 'logout']);
        Route::get('/me', [MobileAuthController::class, 'me']);
    });
});
