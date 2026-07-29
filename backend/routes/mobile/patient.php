<?php

use App\Http\Controllers\Api\V1\Mobile\Patient\PatientMobileController;
use Illuminate\Support\Facades\Route;

/*
| Patient app  —  prefix /api/v1/mobile/patient
| Auth is the email-OTP patient stack (patientAuthVerify), reused from the
| Sprint 8 patient portal. request-otp / verify-otp are public; the rest
| require a patient bearer token.
*/

Route::prefix('patient')->middleware(['restrictIp'])->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/request-otp', [PatientMobileController::class, 'requestOtp']);
        Route::post('/verify-otp', [PatientMobileController::class, 'verifyOtp']);

        Route::middleware(['patientAuthVerify'])->group(function () {
            Route::post('/logout', [PatientMobileController::class, 'logout']);
            Route::get('/me', [PatientMobileController::class, 'me']);
            Route::patch('/profile', [PatientMobileController::class, 'updateProfile']);
        });
    });

    Route::middleware(['patientAuthVerify'])->group(function () {
        // Home + find a doctor
        Route::get('/home', [PatientMobileController::class, 'home']);
        Route::get('/doctors', [PatientMobileController::class, 'doctors']);
        Route::get('/doctors/{id}/slots', [PatientMobileController::class, 'doctorSlots']);

        // Appointments
        Route::get('/appointments', [PatientMobileController::class, 'appointments']);
        Route::post('/appointments', [PatientMobileController::class, 'bookAppointment']);
        Route::post('/appointments/{id}/cancel', [PatientMobileController::class, 'cancelAppointment']);

        // Records
        Route::get('/prescriptions', [PatientMobileController::class, 'prescriptions']);
        Route::get('/prescriptions/{id}/pdf', [PatientMobileController::class, 'prescriptionPdf']);
        Route::get('/lab-reports', [PatientMobileController::class, 'labReports']);
        Route::get('/lab-reports/{id}/pdf', [PatientMobileController::class, 'labReportPdf']);
        Route::get('/timeline', [PatientMobileController::class, 'timeline']);

        // Billing & payments
        Route::get('/bills', [PatientMobileController::class, 'bills']);
        Route::get('/bills/opd/{id}/pdf', [PatientMobileController::class, 'opdBillPdf']);
        Route::get('/bills/ipd/{id}/pdf', [PatientMobileController::class, 'ipdBillPdf']);
        Route::get('/payments', [PatientMobileController::class, 'payments']);
        Route::post('/payments/initiate', [PatientMobileController::class, 'initiatePayment']);
        Route::post('/payments/{transactionRef}/confirm', [PatientMobileController::class, 'confirmPayment']);
    });
});
