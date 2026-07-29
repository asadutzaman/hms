<?php

use App\Http\Controllers\Api\V1\Mobile\OnCall\OnCallMobileController;
use Illuminate\Support\Facades\Route;

/*
| On-call / duty-doctor app  —  prefix /api/v1/mobile/oncall
| Staff bearer token + Doctor role (Super Admin override allowed).
*/

Route::prefix('oncall')->middleware(['restrictIp', 'authVerify', 'mobile.role:Doctor'])->group(function () {
    Route::get('/console', [OnCallMobileController::class, 'console']);

    // DD2 job queue
    Route::get('/jobs', [OnCallMobileController::class, 'jobs']);
    Route::post('/jobs', [OnCallMobileController::class, 'createJob']);
    Route::post('/jobs/{id}/claim', [OnCallMobileController::class, 'claimJob']);
    Route::post('/jobs/{id}/complete', [OnCallMobileController::class, 'completeJob']);

    // DD3 bleeps
    Route::get('/bleeps', [OnCallMobileController::class, 'bleeps']);
    Route::post('/bleeps', [OnCallMobileController::class, 'raiseBleep']);
    Route::post('/bleeps/{id}/acknowledge', [OnCallMobileController::class, 'acknowledgeBleep']);
    Route::post('/bleeps/{id}/escalate', [OnCallMobileController::class, 'escalateBleep']);

    // DD4 A-to-E
    Route::get('/assessments', [OnCallMobileController::class, 'assessments']);
    Route::post('/assessments', [OnCallMobileController::class, 'storeAssessment']);

    // DD5 order sets
    Route::get('/order-sets', [OnCallMobileController::class, 'orderSets']);
    Route::post('/order-sets', [OnCallMobileController::class, 'createOrderSet']);
    Route::post('/order-sets/{id}/apply', [OnCallMobileController::class, 'applyOrderSet']);

    // DD6 ED admission
    Route::get('/ed-board', [OnCallMobileController::class, 'edBoard']);
    Route::post('/ed-triage', [OnCallMobileController::class, 'edTriage']);

    // DD7 handover
    Route::get('/handovers', [OnCallMobileController::class, 'handovers']);
    Route::post('/handovers', [OnCallMobileController::class, 'storeHandover']);
});
