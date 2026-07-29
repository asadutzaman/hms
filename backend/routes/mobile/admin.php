<?php

use App\Http\Controllers\Api\V1\Mobile\Admin\AdminMobileController;
use Illuminate\Support\Facades\Route;

/*
| Administrator app  —  prefix /api/v1/mobile/admin
| Staff bearer token + Super Admin / Administrator role.
*/

Route::prefix('admin')->middleware(['restrictIp', 'authVerify', 'mobile.role:Super Admin,Administrator'])->group(function () {
    Route::get('/dashboard', [AdminMobileController::class, 'dashboard']);
    Route::get('/bed-occupancy', [AdminMobileController::class, 'bedOccupancy']);
    Route::get('/live-ops', [AdminMobileController::class, 'liveOps']);

    // Monitors
    Route::get('/monitors/opd', [AdminMobileController::class, 'opdMonitor']);
    Route::get('/monitors/ipd', [AdminMobileController::class, 'ipdMonitor']);
    Route::get('/monitors/emergency', [AdminMobileController::class, 'emergencyMonitor']);

    // Finance + staffing
    Route::get('/finance', [AdminMobileController::class, 'finance']);
    Route::get('/collections', [AdminMobileController::class, 'collections']);
    Route::get('/staffing', [AdminMobileController::class, 'staffing']);

    // Reports library
    Route::get('/reports', [AdminMobileController::class, 'reports']);
    Route::get('/reports/{key}', [AdminMobileController::class, 'report']);
});
