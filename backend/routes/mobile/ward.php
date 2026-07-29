<?php

use App\Http\Controllers\Api\V1\Mobile\Ward\WardMobileController;
use Illuminate\Support\Facades\Route;

/*
| Ward-doctor (RMO) app  —  prefix /api/v1/mobile/ward
| Staff bearer token + Doctor role (Super Admin override allowed).
*/

Route::prefix('ward')->middleware(['restrictIp', 'authVerify', 'mobile.role:Doctor'])->group(function () {
    Route::get('/dashboard', [WardMobileController::class, 'dashboard']);
    Route::get('/round-list', [WardMobileController::class, 'roundList']);
    Route::get('/admissions/{id}', [WardMobileController::class, 'admission']);

    // WD3 drug chart
    Route::get('/admissions/{admissionId}/drug-chart', [WardMobileController::class, 'drugChart']);
    Route::post('/medication-orders', [WardMobileController::class, 'orderDrug']);
    Route::post('/medication-orders/{id}/discontinue', [WardMobileController::class, 'discontinueDrug']);

    // WD4 investigations + WD5 results
    Route::get('/admissions/{admissionId}/lab-orders', [WardMobileController::class, 'labOrders']);
    Route::post('/lab-orders', [WardMobileController::class, 'createLabOrder']);
    Route::get('/admissions/{admissionId}/radiology-orders', [WardMobileController::class, 'radiologyOrders']);
    Route::post('/radiology-orders', [WardMobileController::class, 'createRadiologyOrder']);
    Route::get('/results-inbox', [WardMobileController::class, 'resultsInbox']);

    // WD6/WD7 discharge
    Route::get('/admissions/{admissionId}/discharge-summary', [WardMobileController::class, 'dischargeSummary']);
    Route::post('/admissions/{admissionId}/discharge-summary/generate', [WardMobileController::class, 'generateDischargeSummary']);
    Route::post('/discharge-summaries/{id}/sign', [WardMobileController::class, 'signDischargeSummary']);

    // WD8 take-home meds
    Route::get('/prescriptions/{prescriptionId}/dispense', [WardMobileController::class, 'takeHomeMeds']);

    // WD9 inpatient record
    Route::get('/admissions/{admissionId}/vitals', [WardMobileController::class, 'vitals']);
    Route::get('/admissions/{admissionId}/fluid-balance', [WardMobileController::class, 'fluidBalance']);
    Route::post('/admissions/{id}/transfer', [WardMobileController::class, 'transfer']);

    // WD2 daily review
    Route::get('/admissions/{admissionId}/daily-reviews', [WardMobileController::class, 'dailyReviews']);
    Route::post('/admissions/{admissionId}/daily-reviews', [WardMobileController::class, 'storeDailyReview']);
});
