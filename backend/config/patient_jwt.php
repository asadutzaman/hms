<?php

return [
    // Deliberately a separate secret from config('jwt.key') — a leaked/reused
    // staff secret must never be able to mint a valid patient portal token or
    // vice versa. See project_hms_sprint8_scope memory for the full rationale
    // behind keeping the patient auth stack fully parallel to the staff one.
    'key' => env('PATIENT_JWT_SECRET', '9f1c6b3e2a7d4f0851c9e6a3b7d2f4819c6e3a1b8d5f2073c9e6a3b1d8f5027'),
    'header' => [
        'alg' => 'HS256',
        'typ' => 'JWT',
    ],
    'exp' => [
        'access' => env('PATIENT_JWT_EXP_ACCESS', 1440), // minutes (24h) — patients aren't re-logging in every 30 min like staff
    ],
];
