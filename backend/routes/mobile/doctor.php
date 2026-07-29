<?php

use App\Http\Controllers\Api\V1\Mobile\Doctor\DoctorMobileController;
use Illuminate\Support\Facades\Route;

/*
| Doctor app  —  prefix /api/v1/mobile/doctor
| Staff bearer token + Doctor role (Super Admin override allowed).
*/

Route::prefix('doctor')->middleware(['restrictIp', 'authVerify', 'mobile.role:Doctor'])->group(function () {
    // D1 dashboard + alerts
    Route::get('/dashboard', [DoctorMobileController::class, 'dashboard']);
    Route::get('/schedule', [DoctorMobileController::class, 'schedule']);
    Route::get('/alerts', [DoctorMobileController::class, 'alerts']);
    Route::post('/alerts/{id}/read', [DoctorMobileController::class, 'markAlertRead']);

    // D2 patient profile
    Route::get('/patients/{id}', [DoctorMobileController::class, 'patient']);
    Route::get('/patients/{id}/history', [DoctorMobileController::class, 'patientHistory']);
    Route::get('/patients/{id}/allergies', [DoctorMobileController::class, 'patientAllergies']);
    Route::get('/patients/{id}/latest-prescription', [DoctorMobileController::class, 'latestPrescription']);
    Route::get('/patients/{id}/lab-orders', [DoctorMobileController::class, 'labOrders']);

    // D3 SOAP notes
    Route::get('/soap-notes', [DoctorMobileController::class, 'soapNotes']);
    Route::post('/soap-notes', [DoctorMobileController::class, 'storeSoapNote']);
    Route::put('/soap-notes/{id}', [DoctorMobileController::class, 'updateSoapNote']);

    // D4 prescription
    Route::post('/visits/{visitId}/prescription', [DoctorMobileController::class, 'savePrescription']);
    Route::get('/recent-drugs', [DoctorMobileController::class, 'recentDrugs']);

    // D5 lab orders + results inbox
    Route::post('/lab-orders', [DoctorMobileController::class, 'createLabOrder']);
    Route::get('/results-inbox', [DoctorMobileController::class, 'resultsInbox']);

    // D6 Code Blue / Rapid Response
    Route::get('/code-blue', [DoctorMobileController::class, 'codeBlueActive']);
    Route::post('/code-blue', [DoctorMobileController::class, 'raiseCodeBlue']);
    Route::post('/code-blue/{id}/respond', [DoctorMobileController::class, 'respondCodeBlue']);
    Route::post('/code-blue/{id}/resolve', [DoctorMobileController::class, 'resolveCodeBlue']);
});
