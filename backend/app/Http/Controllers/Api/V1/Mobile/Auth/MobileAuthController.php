<?php

namespace App\Http\Controllers\Api\V1\Mobile\Auth;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\V1\Mobile\BaseMobileController;
use Illuminate\Http\Request;

/**
 * Staff authentication for the mobile app.
 *
 * These are thin wrappers over the existing bespoke JWT stack (AuthController +
 * JwtService). We do NOT reimplement login logic — we delegate to AuthController
 * (which validates credentials, device/app access, mints DB-backed tokens) and
 * re-wrap its payload in the mobile { success, message, data } envelope. The
 * mobile client should send device_type=mobile.
 *
 * Endpoints (prefix /api/v1/mobile/auth):
 *   POST /login    { username, password, device_type?, mobile_device_token? }
 *   POST /refresh  { refresh_token }
 *   POST /logout   (Bearer)                      [authVerify]
 *   GET  /me       (Bearer)                      [authVerify]
 */
class MobileAuthController extends BaseMobileController
{
    /** POST /api/v1/mobile/auth/login */
    public function login(Request $request)
    {
        // Default device_type to mobile so app clients hit the app_access path.
        if (!$request->filled('device_type')) {
            $request->merge(['device_type' => 'mobile']);
        }

        $response = app(AuthController::class)->login($request);
        $payload  = $response->getData(true);

        return $this->mobileSuccess($payload['data'] ?? $payload, 'Signed in.');
    }

    /** POST /api/v1/mobile/auth/refresh */
    public function refresh(Request $request)
    {
        $response = app(AuthController::class)->refreshToken($request);
        $payload  = $response->getData(true);

        return $this->mobileSuccess($payload['data'] ?? $payload, 'Token refreshed.');
    }

    /** POST /api/v1/mobile/auth/logout  (Bearer) */
    public function logout(Request $request)
    {
        app(AuthController::class)->logout($request);

        return $this->mobileSuccess(null, 'Signed out.');
    }

    /** GET /api/v1/mobile/auth/me  (Bearer) — the "choose session" identity */
    public function me(Request $request)
    {
        $session = $this->session();

        return $this->mobileSuccess([
            'user'   => $session->getUserData(),
            'roles'  => $session->getUserRoleNameList(),
            'scopes' => $session->getUserScopes(),
        ], 'OK');
    }
}
