<?php

use App\Http\Controllers\Api\V1\Mobile\Nurse\NurseMobileController;
use Illuminate\Support\Facades\Route;

/*
| Nurse app  —  prefix /api/v1/mobile/nurse
| Staff bearer token + Nurse role (Super Admin override allowed). Note: no
| dedicated "Nurse" role row is seeded yet, so today only Super Admin passes;
| assign a Nurse role to ward staff to open this app to them.
*/

Route::prefix('nurse')->middleware(['restrictIp', 'authVerify', 'mobile.role:Nurse'])->group(function () {
    Route::get('/shift-board', [NurseMobileController::class, 'shiftBoard']);

    // N2/N6 MAR + barcode verify
    Route::get('/admissions/{admissionId}/mar', [NurseMobileController::class, 'mar']);
    Route::post('/mar/{id}/record', [NurseMobileController::class, 'recordMar']);
    Route::post('/mar/verify-barcode', [NurseMobileController::class, 'verifyBarcode']);

    // N3/N10 vitals
    Route::get('/admissions/{admissionId}/vitals', [NurseMobileController::class, 'vitals']);
    Route::post('/vitals', [NurseMobileController::class, 'recordVitals']);

    // N7 fluid
    Route::get('/admissions/{admissionId}/fluid-balance', [NurseMobileController::class, 'fluidBalance']);
    Route::post('/fluid-balance', [NurseMobileController::class, 'recordFluid']);

    // N8 nursing note
    Route::get('/admissions/{admissionId}/nursing-notes', [NurseMobileController::class, 'nursingNotes']);
    Route::post('/nursing-notes', [NurseMobileController::class, 'storeNursingNote']);

    // N12 transfer
    Route::post('/admissions/{id}/transfer', [NurseMobileController::class, 'transfer']);

    // N9 rapid response
    Route::get('/rapid-response', [NurseMobileController::class, 'rapidResponseActive']);
    Route::post('/rapid-response', [NurseMobileController::class, 'raiseRapidResponse']);

    // N5 task timeline
    Route::get('/tasks', [NurseMobileController::class, 'tasks']);
    Route::post('/tasks/{id}/complete', [NurseMobileController::class, 'completeTask']);

    // N4 handover
    Route::get('/handovers', [NurseMobileController::class, 'handovers']);
    Route::post('/handovers', [NurseMobileController::class, 'storeHandover']);

    // N11 discharge checklist
    Route::get('/admissions/{admissionId}/discharge-checklist', [NurseMobileController::class, 'dischargeChecklist']);
    Route::put('/admissions/{admissionId}/discharge-checklist', [NurseMobileController::class, 'upsertDischargeChecklist']);
});
