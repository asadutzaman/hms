<?php

namespace App\Http\Controllers\PatientPortal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PatientHistoryController;
use App\Services\PatientSessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

/**
 * F-16-05 Patient Timeline View (portal-facing) — delegates straight into
 * PatientHistoryController::timeline(), scoped to the authenticated
 * patient's own id rather than trusting a route param. See that method's
 * docblock for the ?type= filter shared by both the staff and portal callers.
 */
class PatientPortalTimelineController extends Controller
{
    use TraitRestResponse;

    public function myTimeline(Request $request)
    {
        try {
            $patientId = (new PatientSessionService())->init()->getPatientId();
            if (!$patientId) {
                return response()->json(['message' => 'Not authenticated.'], 401);
            }

            return app(PatientHistoryController::class)->timeline((int) $patientId, $request);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
