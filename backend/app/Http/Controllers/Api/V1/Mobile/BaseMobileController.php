<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Services\PatientSessionService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitMobileResponse;

/**
 * Base class for all mobile (/api/v1/mobile) BFF controllers.
 *
 * Provides the consistent mobile response envelope and lazy accessors to the
 * authenticated staff user (via SessionService) or patient (via
 * PatientSessionService). Actor id ALWAYS comes from here — never Auth::id(),
 * which is broken app-wide.
 */
abstract class BaseMobileController extends Controller
{
    use TraitMobileResponse;

    private ?SessionService $sessionService = null;
    private ?PatientSessionService $patientSessionService = null;

    protected function session(): SessionService
    {
        if ($this->sessionService === null) {
            $this->sessionService = (new SessionService())->init();
        }
        return $this->sessionService;
    }

    protected function currentUserId()
    {
        return $this->session()->getUserId();
    }

    protected function currentUser(): array
    {
        return $this->session()->getUserData();
    }

    protected function currentUserRoles(): array
    {
        return $this->session()->getUserRoleNameList() ?? [];
    }

    protected function patientSession(): PatientSessionService
    {
        if ($this->patientSessionService === null) {
            $this->patientSessionService = (new PatientSessionService())->init();
        }
        return $this->patientSessionService;
    }

    protected function currentPatient()
    {
        return $this->patientSession()->getPatient();
    }

    protected function currentPatientId()
    {
        return $this->patientSession()->getPatientId();
    }
}
