<?php

namespace App\Http\Controllers\PatientPortal;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource;
use App\Services\PatientAuthService;
use App\Services\PatientJwtService;
use App\Services\PatientSessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

class PatientAuthController extends Controller
{
    use TraitRestResponse;

    /** POST /patient-portal/auth/request-otp — Body: { email } */
    public function requestOtp(Request $request)
    {
        try {
            $request->validate(['email' => ['required', 'email']]);

            app(PatientAuthService::class)->requestOtp($request->input('email'));

            return $this->successResponse([
                'message' => 'If this email is registered, a login code has been sent.',
            ]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /patient-portal/auth/verify-otp — Body: { email, code } */
    public function verifyOtp(Request $request)
    {
        // NOTE: deliberately no outer DB::beginTransaction()/rollBack() here.
        // PatientAuthService::verifyOtp() manages its own transaction
        // internally; wrapping it in another one here turns that into a mere
        // savepoint, so a caught "wrong code" exception would roll back
        // this outer transaction and silently wipe out the attempt_count
        // increment the service just persisted — defeating the brute-force
        // lockout (found and fixed during Sprint 8 HTTP verification).
        try {
            $request->validate([
                'email' => ['required', 'email'],
                'code'  => ['required', 'string', 'size:6'],
            ]);

            $patient = app(PatientAuthService::class)->verifyOtp($request->input('email'), $request->input('code'));
            $token = app(PatientJwtService::class)->generateToken($patient);

            return $this->successResponse([
                'access_token' => $token,
                'patient'      => (new PatientResource($patient))->toArray($request),
            ]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /patient-portal/auth/logout */
    public function logout(Request $request)
    {
        try {
            $sessionService = (new PatientSessionService())->init();
            $token = $sessionService->getPatientToken();
            if ($token) {
                app(PatientJwtService::class)->revokeToken($token);
            }

            return $this->successResponse(['message' => 'Logged out.']);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /patient-portal/auth/me */
    public function me(Request $request)
    {
        try {
            $patient = (new PatientSessionService())->init()->getPatient();
            if (!$patient) {
                return response()->json(['message' => 'Not authenticated.'], 401);
            }

            return $this->successResponse((new PatientResource($patient))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** PATCH /patient-portal/auth/profile */
    public function updateProfile(Request $request)
    {
        try {
            $patient = (new PatientSessionService())->init()->getPatient();
            if (!$patient) {
                return response()->json(['message' => 'Not authenticated.'], 401);
            }

            $request->validate([
                'primary_phone'   => ['nullable', 'string', 'max:30'],
                'secondary_phone' => ['nullable', 'string', 'max:30'],
                'current_address' => ['nullable', 'string'],
                'current_city'    => ['nullable', 'string', 'max:100'],
                'current_state'   => ['nullable', 'string', 'max:100'],
                'current_country' => ['nullable', 'string', 'max:100'],
                'current_pincode' => ['nullable', 'string', 'max:20'],
                'emergency_contact_name'     => ['nullable', 'string', 'max:255'],
                'emergency_contact_phone'    => ['nullable', 'string', 'max:30'],
                'emergency_contact_relation' => ['nullable', 'string', 'max:100'],
            ]);

            $patient->update($request->only([
                'primary_phone',
                'secondary_phone',
                'current_address',
                'current_city',
                'current_state',
                'current_country',
                'current_pincode',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relation',
            ]));

            return $this->successResponse((new PatientResource($patient->fresh()))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
