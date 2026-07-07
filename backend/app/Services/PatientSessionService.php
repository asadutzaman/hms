<?php

namespace App\Services;

use App\Models\Patient;

/**
 * Thin patient-portal analogue of SessionService — deliberately does NOT
 * pull in roles/scopes/organogram/organization/branch machinery (none of
 * that has meaning for a patient). Just resolves "who is the authenticated
 * patient" from the request's bearer token.
 */
class PatientSessionService
{
    private $jwtService;

    private $patient;

    private $token;

    public function __construct()
    {
        $this->jwtService = new PatientJwtService();
    }

    public function init()
    {
        $this->loadSessionData();
        return $this;
    }

    public function loadSessionData()
    {
        $tokenInfo = $this->jwtService->getTokenInfo();
        if (empty($tokenInfo) || empty($tokenInfo->id)) {
            return;
        }

        $this->token = $this->jwtService->getTokenForRequest();
        $this->patient = Patient::query()->find($tokenInfo->id);
    }

    public function getPatientToken()
    {
        return $this->token ?? '';
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function getPatientId()
    {
        return $this->patient->id ?? null;
    }
}
