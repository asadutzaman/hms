<?php

namespace App\Http\Middleware;

use App\Services\SessionService;
use Closure;
use Illuminate\Http\Request;

/**
 * Role authorization for the mobile (/api/v1/mobile) role apps.
 *
 * The backend authenticates (authVerify) but historically does NOT authorize
 * per-role on routes. Each mobile role app is gated with this middleware, e.g.
 *   ->middleware(['authVerify', 'mobile.role:Doctor'])
 *   ->middleware(['authVerify', 'mobile.role:Doctor,Consultant'])  // any-of
 *
 * Must run AFTER authVerify so a logged-in user is guaranteed. Role names are
 * matched case-insensitively against the user's role_name_list resolved by
 * SessionService (same source the frontend uses for scope checks).
 */
class MobileRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $session = (new SessionService())->init();

        if (empty($session->getUserId())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data'    => null,
            ], 401);
        }

        $userRoles = array_map('strtolower', array_map('strval', $session->getUserRoleNameList() ?? []));

        // Super Admin is a global override — administrators may open any role app.
        if (in_array('super admin', $userRoles, true)) {
            return $next($request);
        }

        $allowed = array_map('strtolower', $roles);

        if (empty(array_intersect($allowed, $userRoles))) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this area.',
                'data'    => null,
            ], 403);
        }

        return $next($request);
    }
}
