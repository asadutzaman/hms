<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  (stateless — loaded via RouteServiceProvider "api" group)
|--------------------------------------------------------------------------
|
| These routes are mounted under the `api` prefix and the stateless `api`
| middleware group (no session/CSRF), which is the correct home for the
| mobile app — unlike the /api/... routes historically defined in web.php,
| which run through the `web` group.
|
| Everything mobile-facing lives under /api/v1/mobile/{app}. Each role app
| has its own routes file under routes/mobile/ that is required below as it
| is built out phase by phase.
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('mobile')->group(function () {
        require __DIR__ . '/mobile/auth.php';
        require __DIR__ . '/mobile/patient.php';
        require __DIR__ . '/mobile/doctor.php';
        require __DIR__ . '/mobile/ward.php';
        require __DIR__ . '/mobile/nurse.php';
        require __DIR__ . '/mobile/oncall.php';
        require __DIR__ . '/mobile/admin.php';
        // require __DIR__ . '/mobile/doctor.php';
        // require __DIR__ . '/mobile/ward.php';
        // require __DIR__ . '/mobile/nurse.php';
        // require __DIR__ . '/mobile/oncall.php';
        // require __DIR__ . '/mobile/admin.php';
    });
});
