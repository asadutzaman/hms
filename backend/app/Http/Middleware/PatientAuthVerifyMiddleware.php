<?php

namespace App\Http\Middleware;

use App\Services\PatientSessionService;
use Closure;

class PatientAuthVerifyMiddleware
{
    /**
     * Handle an incoming request. Mirrors AuthVerifyMiddleware's shape
     * exactly, against the patient portal's own PatientSessionService.
     */
    public function handle($request, Closure $next)
    {
        $sessionService = (new PatientSessionService())->init();
        $patientToken = $sessionService->getPatientToken();

        if ($patientToken) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }
}
