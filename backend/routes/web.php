<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
| This is test
|
*/

Route::get('/', function () {
    return 'app-version-' . app()->version();
});

Route::prefix('api')->group(function () {

    Route::group(['prefix' => 'oauth', 'middleware' => ['restrictIp']], function () {

        # Load Auth State
        Route::post('/load-auth-state', [App\Http\Controllers\VerifyAccessTokenController::class, 'loadAuthState']);
        # Scopes
        Route::post('/load-user-scopes', [App\Http\Controllers\AuthController::class, 'loadUserScopes']);
        # Check Valid Login Info
        Route::post('/check-valid-login-info', [App\Http\Controllers\AuthController::class, 'checkValidLoginInfo']);
        # Switch Organization
        Route::post('/switch-login-organization', [App\Http\Controllers\AuthController::class, 'switchLoginOrganization']);
        # Login
        Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
        # Id Token
        Route::post('/validate-id-token-and-generate-access-token', [App\Http\Controllers\AuthController::class, 'validateIdTokenAndGenerateAccessToken']);
        # Registration
        Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
        Route::post('/verify-email', [App\Http\Controllers\AuthController::class, 'verifyUserEmail']);
        Route::post('/verify-phone', [App\Http\Controllers\AuthController::class, 'verifyUserPhone']);
        # Forgot Password
        Route::post('/forgot-password', [App\Http\Controllers\AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword']);
        # Change Password
        Route::post('/change-password', [App\Http\Controllers\AuthController::class, 'changePassword']);
        # Logout
        Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
        # Token Refresh
        Route::post('/token/refresh', [App\Http\Controllers\AuthController::class, 'refreshToken']);
        # Invite People
        Route::post('/invite-people', [App\Http\Controllers\AuthController::class, 'invitePeople']);
        # send OTP email
        Route::post('/send-otp', [App\Http\Controllers\AuthController::class, 'OtpCode']);
        # password pattern
        Route::get('/password-pattern', [App\Http\Controllers\AuthController::class, 'passwordPattern']);

        // FOR DASHBOARD
        Route::get('/dashboard-stats', [App\Http\Controllers\DashboardController::class, 'getDashboardStats']);
    });

    Route::group(['prefix' => 'example', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::post('/bulk', [App\Http\Controllers\ExampleController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ExampleController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\ExampleController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\ExampleController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\ExampleController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\ExampleController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\ExampleController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\ExampleController::class, 'destroy']);
    });

    Route::group(['prefix' => 'group', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\GroupController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\GroupController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\GroupController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\GroupController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\GroupController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\GroupController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\GroupController::class, 'destroy']);
    });

    Route::group(['prefix' => 'role', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\RoleController::class, 'dropdown']);
        Route::post('/duplicate-role-with-permission', [App\Http\Controllers\RoleController::class, 'duplicateRoleWithPermission']);
        // Get All
        Route::get('/', [App\Http\Controllers\RoleController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\RoleController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\RoleController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\RoleController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\RoleController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\RoleController::class, 'destroy']);
    });

    Route::group(['prefix' => 'user', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::post('/bulk', [App\Http\Controllers\UserController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\UserController::class, 'dropdown']);

        // Create
        Route::post('/update-user-setting', [App\Http\Controllers\UserController::class, 'updateUserSetting']);

        // Get All
        Route::get('/', [App\Http\Controllers\UserController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\UserController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\UserController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\UserController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\UserController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\UserController::class, 'destroy']);
    });

    Route::group(['prefix' => 'resource', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ResourceController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\ResourceController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\ResourceController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\ResourceController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\ResourceController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\ResourceController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\ResourceController::class, 'destroy']);
    });

    Route::group(['prefix' => 'scope', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ScopeController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\ScopeController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\ScopeController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\ScopeController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\ScopeController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\ScopeController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\ScopeController::class, 'destroy']);
    });

    Route::group(['prefix' => 'permission', 'middleware' => ['restrictIp', 'authVerify']], function () {

        Route::post('/check-resource-permission', [App\Http\Controllers\PermissionController::class, 'checkResourcePermission']);

        Route::post('/save-permission', [App\Http\Controllers\PermissionController::class, 'savePermission']);

        Route::post('/role-permission/{roleId}', [App\Http\Controllers\PermissionController::class, 'rolePermission']);

        Route::post('/user-permission/{userId}', [App\Http\Controllers\PermissionController::class, 'userPermission']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\PermissionController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\PermissionController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\PermissionController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\PermissionController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\PermissionController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\PermissionController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\PermissionController::class, 'destroy']);
    });

    Route::group(['prefix' => 'apps-module', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\AppsModuleController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\AppsModuleController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\AppsModuleController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\AppsModuleController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\AppsModuleController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\AppsModuleController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\AppsModuleController::class, 'destroy']);
    });

    Route::group(['prefix' => 'workspace', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\WorkspaceController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\WorkspaceController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\WorkspaceController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\WorkspaceController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\WorkspaceController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\WorkspaceController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\WorkspaceController::class, 'destroy']);
    });

    Route::group(['prefix' => 'organization', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\OrganizationController::class, 'dropdown']);
        //
        Route::get('/getOrganizationTree', [App\Http\Controllers\OrganizationController::class, 'getOrganizationTree']);

        Route::post('/organization-child-ids', [App\Http\Controllers\OrganizationController::class, 'getOrganizationChildIds']);
        // Get All
        Route::get('/', [App\Http\Controllers\OrganizationController::class, 'index']);
        // Get One
        Route::get('/{id}', [App\Http\Controllers\OrganizationController::class, 'show']);
        // Create
        Route::post('/', [App\Http\Controllers\OrganizationController::class, 'store']);
        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\OrganizationController::class, 'update']);
        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\OrganizationController::class, 'updateFields']);
        // Delete
        Route::delete('/{id}', [App\Http\Controllers\OrganizationController::class, 'destroy']);
    });

    Route::group(['prefix' => 'organogram', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\OrganogramController::class, 'dropdown']);

        // Route::get('/syncDoptarOrganogram', [App\Http\Controllers\OrganogramController::class, 'syncDoptarOrganogram']);

        Route::get('/getOrganogramTree', [App\Http\Controllers\OrganogramController::class, 'getOrganogramTree']);

        // Route::get('/getLabTree', [App\Http\Controllers\OrganogramController::class, 'getLabTree']);

        // Route::post('/organogram-child-ids', [App\Http\Controllers\OrganogramController::class, 'getOrganogramChildIds']);

        // Get All
        Route::get('/', [App\Http\Controllers\OrganogramController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\OrganogramController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\OrganogramController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\OrganogramController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\OrganogramController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\OrganogramController::class, 'destroy']);
    });

    Route::group(['prefix' => 'request-access', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\RequestAccessController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\RequestAccessController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\RequestAccessController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\RequestAccessController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\RequestAccessController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\RequestAccessController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\RequestAccessController::class, 'destroy']);
    });

    Route::group(['prefix' => 'employee', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Get By user Id
        Route::get('/getByUserId/{id}', [App\Http\Controllers\EmployeeController::class, 'getByUserId']);
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\EmployeeController::class, 'dropdown']);
        // Get All
        Route::get('/', [App\Http\Controllers\EmployeeController::class, 'index']);
        // Get One
        Route::get('/{id}', [App\Http\Controllers\EmployeeController::class, 'show']);
        // Create
        Route::post('/', [App\Http\Controllers\EmployeeController::class, 'store']);
        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\EmployeeController::class, 'update']);
        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\EmployeeController::class, 'updateFields']);
        // Delete
        Route::delete('/{id}', [App\Http\Controllers\EmployeeController::class, 'destroy']);
        // Get Employee List by Designation id
        Route::post('/getEmployeeListByDesignationIds', [App\Http\Controllers\EmployeeController::class, 'getEmployeeListByDesignationIds']);
    });

    // APPLICANT PROFILE
    Route::group(['prefix' => 'applicant-profile', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ApplicantProfileController::class, 'dropdown']);
        // Git By Where
        Route::get('/getByWhere', [App\Http\Controllers\ApplicantProfileController::class, 'getByWhere']);
        //profile details
        Route::get('/applicant/details/{id}', [App\Http\Controllers\ApplicantProfileController::class, 'profileDetails']);
        // Get All
        Route::get('/', [App\Http\Controllers\ApplicantProfileController::class, 'index']);
        // Get One
        Route::get('/{id}', [App\Http\Controllers\ApplicantProfileController::class, 'show']);
        // Create
        Route::post('/', [App\Http\Controllers\ApplicantProfileController::class, 'store']);
        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\ApplicantProfileController::class, 'update']);
        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\ApplicantProfileController::class, 'updateFields']);
        // Delete
        Route::delete('/{id}', [App\Http\Controllers\ApplicantProfileController::class, 'destroy']);
    });

    Route::group(['prefix' => 'option', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Load
        Route::get('/load', [App\Http\Controllers\OptionController::class, 'load']); // Done

        // Save
        Route::post('/save', [App\Http\Controllers\OptionController::class, 'save']); // Done
    });

    Route::group(['prefix' => 'session-service', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/get-session-data', [App\Http\Controllers\SessionController::class, 'getSessionServiceData']);
    });

    Route::group(['prefix' => 'artisan', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/flush-all-cache', [App\Http\Controllers\ArtisanCommandController::class, 'flushAllCache']);
        Route::post('/scheduler/run', [App\Http\Controllers\ArtisanCommandController::class, 'schedule']);
    });

    Route::group(['prefix' => 'file'], function () {
        // File Upload
        Route::post('/upload', [App\Http\Controllers\FileController::class, 'upload']);

        Route::post('/get-multiple-files', [App\Http\Controllers\FileController::class, 'getMultipleFiles']);

        // File download
        Route::get('/download/{fileId}', [App\Http\Controllers\FileController::class, 'download']);

        // File download
        Route::get('/download-file', [App\Http\Controllers\FileController::class, 'downloadFile']);

        // Get File
        Route::get('/{id}', [App\Http\Controllers\FileController::class, 'show']); // Done

        // Delete File
        Route::delete('/{id}', [App\Http\Controllers\FileController::class, 'destroy']); // Done
    });

    // BACK_OFFICE_SETUP: START
    Route::group(['prefix' => 'enum', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\EnumController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\EnumController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\EnumController::class, 'show']);
    });

    Route::group(['prefix' => 'application-settings', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ApplicationSettingController::class, 'dropdown']);

        // Create
        Route::post('/', [App\Http\Controllers\ApplicationSettingController::class, 'store']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\ApplicationSettingController::class, 'show']);
    });

    Route::group(['prefix' => 'unit', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\UnitController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\UnitController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\UnitController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\UnitController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\UnitController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\UnitController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\UnitController::class, 'destroy']);
    });

    Route::group(['prefix' => 'department', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\DepartmentController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\DepartmentController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\DepartmentController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\DepartmentController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\DepartmentController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\DepartmentController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\DepartmentController::class, 'destroy']);
    });

    Route::group(['prefix' => 'designation', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\DesignationController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\DesignationController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\DesignationController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\DesignationController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\DesignationController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\DesignationController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\DesignationController::class, 'destroy']);
    });

    Route::group(['prefix' => 'requisition-item-limit', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\RequisitionItemLimitController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\RequisitionItemLimitController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\RequisitionItemLimitController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\RequisitionItemLimitController::class, 'show']);
        Route::post('/', [App\Http\Controllers\RequisitionItemLimitController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\RequisitionItemLimitController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\RequisitionItemLimitController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\RequisitionItemLimitController::class, 'destroy']);
    });

    Route::group(['prefix' => 'item-category', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ItemCategoryController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\ItemCategoryController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\ItemCategoryController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\ItemCategoryController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\ItemCategoryController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\ItemCategoryController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\ItemCategoryController::class, 'destroy']);
    });

    Route::group(['prefix' => 'brand', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\BrandController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\BrandController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\BrandController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\BrandController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\BrandController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\BrandController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\BrandController::class, 'destroy']);
    });

    Route::group(['prefix' => 'approver-group', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::get('/test', [App\Http\Controllers\ApproverGroupController::class, 'test']);
        Route::post('/bulk', [App\Http\Controllers\ApproverGroupController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ApproverGroupController::class, 'dropdown']);
        Route::get('/member-dropdown', [App\Http\Controllers\ApproverGroupController::class, 'memberDropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\ApproverGroupController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\ApproverGroupController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\ApproverGroupController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\ApproverGroupController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\ApproverGroupController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\ApproverGroupController::class, 'destroy']);
    });

    Route::group(['prefix' => 'branch', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::get('/test', [App\Http\Controllers\BranchController::class, 'test']);
        Route::post('/bulk', [App\Http\Controllers\BranchController::class, 'bulk']);
        Route::get('/getBranchTree', [App\Http\Controllers\BranchController::class, 'getBranchTree']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\BranchController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\BranchController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\BranchController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\BranchController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\BranchController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\BranchController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\BranchController::class, 'destroy']);
    });

    Route::group(['prefix' => 'shelve', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::get('/test', [App\Http\Controllers\ShelveController::class, 'test']);
        Route::post('/bulk', [App\Http\Controllers\ShelveController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ShelveController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\ShelveController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\ShelveController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\ShelveController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\ShelveController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\ShelveController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\ShelveController::class, 'destroy']);
    });

    Route::group(['prefix' => 'logistic', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::get('/test', [App\Http\Controllers\LogisticController::class, 'test']);
        Route::post('/bulk', [App\Http\Controllers\LogisticController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\LogisticController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\LogisticController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\LogisticController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\LogisticController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\LogisticController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\LogisticController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\LogisticController::class, 'destroy']);
    });

    Route::group(['prefix' => 'attribute', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::get('/test', [App\Http\Controllers\AttributeController::class, 'test']);
        Route::post('/bulk', [App\Http\Controllers\AttributeController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\AttributeController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\AttributeController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\AttributeController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\AttributeController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\AttributeController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\AttributeController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\AttributeController::class, 'destroy']);
    });

    Route::group(['prefix' => 'attribute-value', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::get('/test', [App\Http\Controllers\AttributeValueController::class, 'test']);
        Route::post('/bulk', [App\Http\Controllers\AttributeValueController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\AttributeValueController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\AttributeValueController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\AttributeValueController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\AttributeValueController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\AttributeValueController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\AttributeValueController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\AttributeValueController::class, 'destroy']);
    });

    Route::group(['prefix' => 'item', 'middleware' => ['restrictIp', 'authVerify']], function () {

        Route::get('/export/{file}', [App\Http\Controllers\ItemController::class, 'export']);
        // Route::post('/update-item-name', [App\Http\Controllers\ItemController::class, 'updateItemName']);
        // Route::get('/check-latest-code', [App\Http\Controllers\ItemController::class, 'checkLatestCode']);

        // Bulk Actions
        Route::post('/bulk-store', [App\Http\Controllers\ItemController::class, 'bulkStore']);
        Route::post('/bulk', [App\Http\Controllers\ItemController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ItemController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\ItemController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\ItemController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\ItemController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\ItemController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\ItemController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\ItemController::class, 'destroy']);
    });

    // DRUG (Pharmacy Master)
    Route::group(['prefix' => 'drug', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\DrugController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\DrugController::class, 'dropdown']);
        Route::get('/generics', [App\Http\Controllers\DrugController::class, 'generics']);
        Route::get('/', [App\Http\Controllers\DrugController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\DrugController::class, 'show']);
        Route::get('/{id}/substitutes', [App\Http\Controllers\DrugController::class, 'substitutes']);
        Route::get('/{id}/stock', [App\Http\Controllers\DrugController::class, 'stock']);
        Route::post('/', [App\Http\Controllers\DrugController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\DrugController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\DrugController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\DrugController::class, 'destroy']);
    });

    // DRUG INTERACTION
    Route::group(['prefix' => 'drug-interaction', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\DrugInteractionController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\DrugInteractionController::class, 'dropdown']);
        Route::post('/check', [App\Http\Controllers\DrugInteractionController::class, 'check']);
        Route::get('/', [App\Http\Controllers\DrugInteractionController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\DrugInteractionController::class, 'show']);
        Route::post('/', [App\Http\Controllers\DrugInteractionController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\DrugInteractionController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\DrugInteractionController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\DrugInteractionController::class, 'destroy']);
    });

    Route::group(['prefix' => 'workflow', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Git By Where
        Route::get('/getByWhere', [App\Http\Controllers\WorkflowController::class, 'getByWhere']);

        // Bulk Actions
        Route::post('/bulk', [App\Http\Controllers\WorkflowController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\WorkflowController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\WorkflowController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\WorkflowController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\WorkflowController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\WorkflowController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\WorkflowController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\WorkflowController::class, 'destroy']);
    });

    Route::group(['prefix' => 'workflow-step', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Git By Where
        Route::get('/getByWhere', [App\Http\Controllers\WorkflowStepController::class, 'getByWhere']);

        // Bulk Actions
        Route::post('/bulk', [App\Http\Controllers\WorkflowStepController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\WorkflowStepController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\WorkflowStepController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\WorkflowStepController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\WorkflowStepController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\WorkflowStepController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\WorkflowStepController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\WorkflowStepController::class, 'destroy']);
    });

    Route::group(['prefix' => 'workflow-transition', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Git By Where
        Route::get('/getByWhere', [App\Http\Controllers\WorkflowTransitionController::class, 'getByWhere']);

        // Get All
        Route::get('/', [App\Http\Controllers\WorkflowTransitionController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\WorkflowTransitionController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\WorkflowTransitionController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\WorkflowTransitionController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\WorkflowTransitionController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\WorkflowTransitionController::class, 'destroy']);
    });

    Route::group(['prefix' => 'govt-holiday', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Git By Where
        Route::get('/getByWhere', [App\Http\Controllers\GovtHolidayController::class, 'getByWhere']);

        // Get All
        Route::get('/', [App\Http\Controllers\GovtHolidayController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\GovtHolidayController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\GovtHolidayController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\GovtHolidayController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\GovtHolidayController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\GovtHolidayController::class, 'destroy']);
    });
    // BACK_OFFICE_SETUP: END

    // RREQISITION
    Route::group(['prefix' => 'requisition', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Item Wise Disbursement Details
        Route::get('/item-disbursement-details', [App\Http\Controllers\RequisitionController::class, 'getItemDisbursementDetails']);

        Route::get('/export/{file}', [App\Http\Controllers\RequisitionController::class, 'export']);
        // Bulk Actions
        Route::post('/bulk-store', [App\Http\Controllers\RequisitionController::class, 'bulkStore']);
        Route::post('/bulk', [App\Http\Controllers\RequisitionController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\RequisitionController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\RequisitionController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\RequisitionController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\RequisitionController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\RequisitionController::class, 'update']);
        Route::put('/acknowledge/{id}', [App\Http\Controllers\RequisitionController::class, 'acknowledge']);

        // Convert shortfall to a Purchase Order
        Route::post('/{id}/convert-to-po', [App\Http\Controllers\RequisitionController::class, 'convertToPurchaseOrder']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\RequisitionController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\RequisitionController::class, 'destroy']);
    });

    // REQUISITION APPROVAL
    Route::group(['prefix' => 'approval-requisition', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Get All
        Route::get('/', [App\Http\Controllers\Approval\RequisitionApprovalController::class, 'index']);

        // Shortfall check (against warehouse stock)
        Route::get('/{id}/shortfall', [App\Http\Controllers\Approval\RequisitionApprovalController::class, 'shortfall']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\Approval\RequisitionApprovalController::class, 'show']);
    });

    // GOODS RECEIVE NOTE
    Route::group(['prefix' => 'goods-receive-note', 'middleware' => ['restrictIp', 'authVerify']], function () {

        // Item Wise Grn Details
        Route::get('/item-grn-details', [App\Http\Controllers\GoodsReceiveNoteController::class, 'getItemGrnDetails']);

        Route::get('/export/{file}', [App\Http\Controllers\GoodsReceiveNoteController::class, 'export']);
        // Route::post('/update-item-name', [App\Http\Controllers\GoodsReceiveNoteController::class, 'updateItemName']);
        // Route::get('/check-latest-code', [App\Http\Controllers\GoodsReceiveNoteController::class, 'checkLatestCode']);

        // Bulk Actions
        Route::post('/bulk-store', [App\Http\Controllers\GoodsReceiveNoteController::class, 'bulkStore']);
        Route::post('/bulk', [App\Http\Controllers\GoodsReceiveNoteController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\GoodsReceiveNoteController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\GoodsReceiveNoteController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\GoodsReceiveNoteController::class, 'show']);
        Route::put('/update-process-status/{id}', [App\Http\Controllers\GoodsReceiveNoteController::class, 'updateProcesStatus']);

        // Create
        Route::post('/', [App\Http\Controllers\GoodsReceiveNoteController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\GoodsReceiveNoteController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\GoodsReceiveNoteController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\GoodsReceiveNoteController::class, 'destroy']);
    });

    // GOODS RECEIVE NOTE APPROVAL
    Route::group(['prefix' => 'approval-goods-receive-note', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Get All
        Route::get('/', [App\Http\Controllers\Approval\GoodsReceiveNoteApprovalController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\Approval\GoodsReceiveNoteApprovalController::class, 'show']);
    });

    // PURCHASE ORDER
    Route::group(['prefix' => 'purchase-order', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\PurchaseOrderController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\PurchaseOrderController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\PurchaseOrderController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'show']);
        Route::get('/{id}/items-for-grn', [App\Http\Controllers\PurchaseOrderController::class, 'itemsForGrn']);
        Route::post('/', [App\Http\Controllers\PurchaseOrderController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'destroy']);
    });

    // PURCHASE ORDER APPROVAL
    Route::group(['prefix' => 'approval-purchase-order', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Get All
        Route::get('/', [App\Http\Controllers\Approval\PurchaseOrderApprovalController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\Approval\PurchaseOrderApprovalController::class, 'show']);
    });

    // VENDOR QUOTE
    Route::group(['prefix' => 'vendor-quote', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\VendorQuoteController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\VendorQuoteController::class, 'dropdown']);
        Route::get('/compare', [App\Http\Controllers\VendorQuoteController::class, 'compare']);
        Route::get('/', [App\Http\Controllers\VendorQuoteController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\VendorQuoteController::class, 'show']);
        Route::post('/', [App\Http\Controllers\VendorQuoteController::class, 'store']);
        Route::post('/{id}/select-winner', [App\Http\Controllers\VendorQuoteController::class, 'selectWinner']);
        Route::put('/{id}', [App\Http\Controllers\VendorQuoteController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\VendorQuoteController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\VendorQuoteController::class, 'destroy']);
    });

    // RATE CONTRACT
    Route::group(['prefix' => 'rate-contract', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\RateContractController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\RateContractController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\RateContractController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\RateContractController::class, 'show']);
        Route::post('/', [App\Http\Controllers\RateContractController::class, 'store']);
        Route::post('/{id}/submit', [App\Http\Controllers\RateContractController::class, 'submit']);
        Route::post('/{id}/approve', [App\Http\Controllers\RateContractController::class, 'approve']);
        Route::post('/{id}/reject', [App\Http\Controllers\RateContractController::class, 'reject']);
        Route::put('/{id}', [App\Http\Controllers\RateContractController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\RateContractController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\RateContractController::class, 'destroy']);
    });

    // STOCK TRANSFER APPROVAL
    Route::group(['prefix' => 'approval-stock-transfer', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Get All
        Route::get('/', [App\Http\Controllers\Approval\StockTransferApprovalController::class, 'index']);
        // Get One
        Route::get('/{id}', [App\Http\Controllers\Approval\StockTransferApprovalController::class, 'show']);
    });

    // STOCK ADJUSTMENT
    Route::group(['prefix' => 'stock-adjustment', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/update-process-status/{id}', [App\Http\Controllers\StockAdjustmentController::class, 'stockProcessStatusUpdate']);
        Route::get('/export/{file}', [App\Http\Controllers\StockAdjustmentController::class, 'export']);
        // Route::post('/update-item-name', [App\Http\Controllers\StockAdjustmentController::class, 'updateItemName']);
        // Route::get('/check-latest-code', [App\Http\Controllers\StockAdjustmentController::class, 'checkLatestCode']);

        // Bulk Actions
        Route::post('/bulk-store', [App\Http\Controllers\StockAdjustmentController::class, 'bulkStore']);
        Route::post('/bulk', [App\Http\Controllers\StockAdjustmentController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\StockAdjustmentController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\StockAdjustmentController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\StockAdjustmentController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\StockAdjustmentController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\StockAdjustmentController::class, 'update']);
        Route::put('/update-process-status/{id}', [App\Http\Controllers\StockAdjustmentController::class, 'updateProcessStatus']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\StockAdjustmentController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\StockAdjustmentController::class, 'destroy']);
    });

    // STOCK ADJUSTMENT APPROVAL
    Route::group(['prefix' => 'approval-stock-adjustment', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Get All
        Route::get('/', [App\Http\Controllers\Approval\StockAdjustmentApprovalController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\Approval\StockAdjustmentApprovalController::class, 'show']);
    });

    // ITEM CONSUMPTION
    Route::group(['prefix' => 'item-consumption', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/export/{file}', [App\Http\Controllers\ItemConsumptionController::class, 'export']);
        // Route::post('/update-item-name', [App\Http\Controllers\ItemConsumptionController::class, 'updateItemName']);
        // Route::get('/check-latest-code', [App\Http\Controllers\ItemConsumptionController::class, 'checkLatestCode']);
        Route::get('/branch-item-stock', [App\Http\Controllers\ItemConsumptionController::class, 'getBranchItemStock']);
        // Bulk Actions
        Route::post('/bulk-store', [App\Http\Controllers\ItemConsumptionController::class, 'bulkStore']);
        Route::post('/bulk', [App\Http\Controllers\ItemConsumptionController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\ItemConsumptionController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\ItemConsumptionController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\ItemConsumptionController::class, 'show']);
        Route::put('/update-process-status/{id}', [App\Http\Controllers\ItemConsumptionController::class, 'updateProcesStatus']);

        // Create
        Route::post('/', [App\Http\Controllers\ItemConsumptionController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\ItemConsumptionController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\ItemConsumptionController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\ItemConsumptionController::class, 'destroy']);
    });

    // STOCK TRANSFER
    Route::group(['prefix' => 'stock-transfer', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/update-process-status/{id}', [App\Http\Controllers\StockTransferController::class, 'stockProcessStatusUpdate']);
        Route::get('/export/{file}', [App\Http\Controllers\StockTransferController::class, 'export']);
        // Route::post('/update-item-name', [App\Http\Controllers\StockTransferController::class, 'updateItemName']);
        // Route::get('/check-latest-code', [App\Http\Controllers\StockTransferController::class, 'checkLatestCode']);

        // Bulk Actions
        Route::post('/bulk-store', [App\Http\Controllers\StockTransferController::class, 'bulkStore']);
        Route::post('/bulk', [App\Http\Controllers\StockTransferController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\StockTransferController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\StockTransferController::class, 'index']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\StockTransferController::class, 'show']);

        // Create
        Route::post('/', [App\Http\Controllers\StockTransferController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\StockTransferController::class, 'update']);
        Route::put('/update-process-status/{id}', [App\Http\Controllers\StockTransferController::class, 'updateProcessStatus']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\StockTransferController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\StockTransferController::class, 'destroy']);
    });

    Route::group(['prefix' => 'supplier', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/dropdown', [App\Http\Controllers\SupplierController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\SupplierController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\SupplierController::class, 'show']);
        Route::post('/', [App\Http\Controllers\SupplierController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\SupplierController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\SupplierController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\SupplierController::class, 'destroy']);
    });

    // REPORT
    Route::group(['prefix' => 'report', 'middleware' => ['restrictIp', 'authVerify']], function () {;
        Route::get('/requisition-analytic', [App\Http\Controllers\Report\RequisitionAnalyticReportController::class, 'getRequisitionAnalytic']);
        Route::get('/requisition-analytic-export', [App\Http\Controllers\Report\RequisitionAnalyticReportController::class, 'getRequisitionAnalyticExport']);
        Route::get('/item-stock', [App\Http\Controllers\Report\ItemStockReportController::class, 'getItemStockList']);
        Route::get('/item-stock-export', [App\Http\Controllers\Report\ItemStockReportController::class, 'getItemStockExport']);
        Route::get('/item-lifecycle-stock', [App\Http\Controllers\Report\ItemStockReportController::class, 'getItemLifecycleStockReport']);
        Route::get('/item-low-stock', [App\Http\Controllers\Report\ItemLowStockReportController::class, 'getItemLowStocktList']);
        Route::get('/item-low-stock-export', [App\Http\Controllers\Report\ItemLowStockReportController::class, 'getItemLowStockExport']);
        Route::get('/item-requisition-status', [App\Http\Controllers\Report\ItemRequisitionStatusReportController::class, 'getItemRequisitionStatusReportList']);
        Route::get('/item-requisition-status-export', [App\Http\Controllers\Report\ItemRequisitionStatusReportController::class, 'getItemRequisitionStatusReportExport']);
        Route::get('/requester-wise-disbursement', [App\Http\Controllers\Report\RequesterWiseDisbursementReportController::class, 'getRequesterWiseDisbursementList']);
        Route::get('/requester-wise-disbursement-export', [App\Http\Controllers\Report\RequesterWiseDisbursementReportController::class, 'getRequesterWiseDisbursementExport']);
        Route::get('/item-wise-disbursement', [App\Http\Controllers\Report\ItemWiseDisbursementReportController::class, 'getItemWiseDisbursementList']);
        Route::get('/item-wise-disbursement-export', [App\Http\Controllers\Report\ItemWiseDisbursementReportController::class, 'getItemWiseDisbursementExport']);
        Route::get('/branch-wise-disbursement', [App\Http\Controllers\Report\ThanaWiseDisbursementReportController::class, 'getThanaWiseDisbursementList']);
        Route::get('/branch-wise-disbursement-export', [App\Http\Controllers\Report\ThanaWiseDisbursementReportController::class, 'getThanaWiseDisbursementExport']);
        Route::get('/drug-expiry', [App\Http\Controllers\Report\DrugExpiryReportController::class, 'getExpiryList']);
        Route::get('/drug-expiry-export', [App\Http\Controllers\Report\DrugExpiryReportController::class, 'getExpiryListExport']);
        Route::get('/daily-collection', [App\Http\Controllers\Report\DailyCollectionReportController::class, 'getDailyCollectionList']);
        Route::get('/daily-collection-export', [App\Http\Controllers\Report\DailyCollectionReportController::class, 'getDailyCollectionExport']);
        Route::get('/controlled-drug-register', [App\Http\Controllers\Report\ControlledDrugRegisterReportController::class, 'getRegisterList']);
        Route::get('/item-low-stock-alerts', [App\Http\Controllers\Report\ItemLowStockReportController::class, 'alerts']);
    });

    // EXPORT
    Route::group(['prefix' => 'export', 'middleware' => ['restrictIp', 'authVerify']], function () {});

    // PATIENT
    Route::group(['prefix' => 'patient', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::post('/bulk', [App\Http\Controllers\PatientController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\PatientController::class, 'dropdown']);

        // Get All
        Route::get('/', [App\Http\Controllers\PatientController::class, 'index']);

        // Static sub-paths must be declared BEFORE the /{id} catch-all,
        // otherwise Laravel matches e.g. "get-by-where" as $id and passes it
        // to the bigint `id` column, which Postgres rejects with:
        //   SQLSTATE[22P02]: invalid input syntax for type bigint
        Route::get('/get-by-where', [App\Http\Controllers\PatientController::class, 'getByWhere']);

        // Merge duplicate patient records
        Route::post('/merge', [App\Http\Controllers\PatientController::class, 'merge']);

        // Get One
        Route::get('/{id}', [App\Http\Controllers\PatientController::class, 'show']);

        // Audit trail
        Route::get('/{id}/audit-log', [App\Http\Controllers\PatientController::class, 'auditLog']);

        // Allergies
        Route::get('/{id}/allergies', [App\Http\Controllers\PatientController::class, 'allergies']);

        // Create
        Route::post('/', [App\Http\Controllers\PatientController::class, 'store']);

        // Update (Check Validation)
        Route::put('/{id}', [App\Http\Controllers\PatientController::class, 'update']);

        // Update Partial (Without Validation)
        Route::patch('/{id}', [App\Http\Controllers\PatientController::class, 'updateFields']);

        // Delete
        Route::delete('/{id}', [App\Http\Controllers\PatientController::class, 'destroy']);
    });

    // PATIENT ALLERGY
    Route::group(['prefix' => 'patient-allergy', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\PatientAllergyController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\PatientAllergyController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\PatientAllergyController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\PatientAllergyController::class, 'show']);
        Route::post('/', [App\Http\Controllers\PatientAllergyController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\PatientAllergyController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\PatientAllergyController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\PatientAllergyController::class, 'destroy']);
    });

    // APPOINTMENT
    Route::group(['prefix' => 'appointment', 'middleware' => ['restrictIp', 'authVerify']], function () {
        // Bulk Actions
        Route::post('/bulk', [App\Http\Controllers\AppointmentController::class, 'bulk']);

        // Drop Down List
        Route::get('/dropdown', [App\Http\Controllers\AppointmentController::class, 'dropdown']);

        // Standard CRUD
        Route::get('/', [App\Http\Controllers\AppointmentController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\AppointmentController::class, 'show']);
        Route::post('/', [App\Http\Controllers\AppointmentController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\AppointmentController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\AppointmentController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\AppointmentController::class, 'destroy']);

        // Walk-in quick entry (force type=WALK_IN server-side)
        Route::post('/walk-in', [App\Http\Controllers\AppointmentController::class, 'walkIn']);

        // Lifecycle actions
        Route::post('/{id}/cancel', [App\Http\Controllers\AppointmentController::class, 'cancel']);
        Route::post('/{id}/reschedule', [App\Http\Controllers\AppointmentController::class, 'reschedule']);
        Route::post('/{id}/check-in', [App\Http\Controllers\AppointmentController::class, 'checkIn']);
        Route::post('/{id}/start', [App\Http\Controllers\AppointmentController::class, 'startConsultation']);
        Route::post('/{id}/complete', [App\Http\Controllers\AppointmentController::class, 'complete']);
        Route::post('/{id}/no-show', [App\Http\Controllers\AppointmentController::class, 'noShow']);

        // Query helpers
        Route::get('/available-slots', [App\Http\Controllers\AppointmentController::class, 'availableSlots']);
        Route::get('/queue/doctor/{doctorId}', [App\Http\Controllers\AppointmentController::class, 'queue']);
        Route::get('/{id}/audit-log', [App\Http\Controllers\AppointmentController::class, 'auditLog']);
    });

    // DOCTOR SCHEDULE
    Route::group(['prefix' => 'doctor-schedule', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\DoctorScheduleController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\DoctorScheduleController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\DoctorScheduleController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\DoctorScheduleController::class, 'show']);
        Route::post('/', [App\Http\Controllers\DoctorScheduleController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\DoctorScheduleController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\DoctorScheduleController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\DoctorScheduleController::class, 'destroy']);

        // Slot materialization
        Route::post('/{id}/generate-slots', [App\Http\Controllers\DoctorScheduleController::class, 'generateSlots']);
        Route::get('/available-slots', [App\Http\Controllers\DoctorScheduleController::class, 'availableSlots']);
    });

    // APPOINTMENT WAITLIST
    Route::group(['prefix' => 'appointment-waitlist', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\AppointmentWaitlistController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\AppointmentWaitlistController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\AppointmentWaitlistController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\AppointmentWaitlistController::class, 'show']);
        Route::post('/', [App\Http\Controllers\AppointmentWaitlistController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\AppointmentWaitlistController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\AppointmentWaitlistController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\AppointmentWaitlistController::class, 'destroy']);

        Route::post('/{id}/notify', [App\Http\Controllers\AppointmentWaitlistController::class, 'notify']);
        Route::post('/{id}/convert', [App\Http\Controllers\AppointmentWaitlistController::class, 'convert']);
        Route::post('/{id}/expire', [App\Http\Controllers\AppointmentWaitlistController::class, 'expire']);
    });

    // OPD VISIT
    Route::group(['prefix' => 'opd-visit', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdVisitController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdVisitController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdVisitController::class, 'index']);
        Route::get('/today', [App\Http\Controllers\OpdVisitController::class, 'today']);
        Route::get('/display-board', [App\Http\Controllers\OpdVisitController::class, 'displayBoard']);
        Route::get('/{id}', [App\Http\Controllers\OpdVisitController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdVisitController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdVisitController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdVisitController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdVisitController::class, 'destroy']);

        Route::post('/{id}/transition', [App\Http\Controllers\OpdVisitController::class, 'transition']);
        Route::post('/{id}/cancel', [App\Http\Controllers\OpdVisitController::class, 'cancel']);
        Route::post('/{id}/call', [App\Http\Controllers\OpdVisitController::class, 'call']);
        Route::post('/{id}/follow-up', [App\Http\Controllers\OpdVisitController::class, 'scheduleFollowUp']);
        Route::get('/{id}/audit-log', [App\Http\Controllers\OpdVisitController::class, 'auditLog']);
    });

    // OPD VITAL
    Route::group(['prefix' => 'opd-vital', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdVitalController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdVitalController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdVitalController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\OpdVitalController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdVitalController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdVitalController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdVitalController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdVitalController::class, 'destroy']);
    });

    // OPD DIAGNOSIS
    Route::group(['prefix' => 'opd-diagnosis', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdDiagnosisController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdDiagnosisController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdDiagnosisController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\OpdDiagnosisController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdDiagnosisController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdDiagnosisController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdDiagnosisController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdDiagnosisController::class, 'destroy']);
    });

    // OPD PRESCRIPTION
    Route::group(['prefix' => 'opd-prescription', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdPrescriptionController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdPrescriptionController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdPrescriptionController::class, 'index']);
        Route::post('/visit/{visitId}', [App\Http\Controllers\OpdPrescriptionController::class, 'saveForVisit']);
        Route::get('/{id}/pdf', [App\Http\Controllers\OpdPrescriptionController::class, 'pdf']);
        Route::get('/{id}', [App\Http\Controllers\OpdPrescriptionController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdPrescriptionController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdPrescriptionController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdPrescriptionController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdPrescriptionController::class, 'destroy']);
    });

    // OPD PRESCRIPTION ITEM
    Route::group(['prefix' => 'opd-prescription-item', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdPrescriptionItemController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdPrescriptionItemController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdPrescriptionItemController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\OpdPrescriptionItemController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdPrescriptionItemController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdPrescriptionItemController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdPrescriptionItemController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdPrescriptionItemController::class, 'destroy']);
    });

    // DOCTOR PORTAL
    Route::group(['prefix' => 'doctor-portal', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/dashboard', [App\Http\Controllers\DoctorPortalController::class, 'dashboard']);
        Route::get('/appointments', [App\Http\Controllers\DoctorPortalController::class, 'appointments']);
        Route::get('/patient-history/{patientId}', [App\Http\Controllers\DoctorPortalController::class, 'patientHistory']);
    });

    // PRESCRIPTION TEMPLATE
    Route::group(['prefix' => 'prescription-template', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\PrescriptionTemplateController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\PrescriptionTemplateController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\PrescriptionTemplateController::class, 'index']);
        Route::get('/{id}/apply', [App\Http\Controllers\PrescriptionTemplateController::class, 'apply']);
        Route::get('/{id}', [App\Http\Controllers\PrescriptionTemplateController::class, 'show']);
        Route::post('/', [App\Http\Controllers\PrescriptionTemplateController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\PrescriptionTemplateController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\PrescriptionTemplateController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\PrescriptionTemplateController::class, 'destroy']);
    });

    // PRESCRIPTION DISPENSE
    Route::group(['prefix' => 'prescription-dispense', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/', [App\Http\Controllers\PrescriptionDispenseController::class, 'index']);
        Route::get('/for-prescription/{prescriptionId}', [App\Http\Controllers\PrescriptionDispenseController::class, 'forPrescription']);
        Route::post('/shortfall', [App\Http\Controllers\PrescriptionDispenseController::class, 'shortfall']);
        Route::post('/{prescriptionId}', [App\Http\Controllers\PrescriptionDispenseController::class, 'dispense']);
        Route::get('/{id}', [App\Http\Controllers\PrescriptionDispenseController::class, 'show']);
    });

    // REFERRAL
    Route::group(['prefix' => 'referral', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\ReferralController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\ReferralController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\ReferralController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\ReferralController::class, 'show']);
        Route::post('/', [App\Http\Controllers\ReferralController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\ReferralController::class, 'update']);
        Route::patch('/{id}/status', [App\Http\Controllers\ReferralController::class, 'updateStatus']);
        Route::patch('/{id}', [App\Http\Controllers\ReferralController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\ReferralController::class, 'destroy']);
    });

    // DIAGNOSIS TEMPLATE
    Route::group(['prefix' => 'diagnosis-template', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\DiagnosisTemplateController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\DiagnosisTemplateController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\DiagnosisTemplateController::class, 'index']);
        Route::get('/{id}/apply', [App\Http\Controllers\DiagnosisTemplateController::class, 'apply']);
        Route::get('/{id}', [App\Http\Controllers\DiagnosisTemplateController::class, 'show']);
        Route::post('/', [App\Http\Controllers\DiagnosisTemplateController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\DiagnosisTemplateController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\DiagnosisTemplateController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\DiagnosisTemplateController::class, 'destroy']);
    });

    // ICD-10 CODE
    Route::group(['prefix' => 'icd10-code', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\Icd10CodeController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\Icd10CodeController::class, 'dropdown']);
        Route::get('/search', [App\Http\Controllers\Icd10CodeController::class, 'search']);
        Route::get('/', [App\Http\Controllers\Icd10CodeController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Icd10CodeController::class, 'show']);
        Route::post('/', [App\Http\Controllers\Icd10CodeController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Icd10CodeController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\Icd10CodeController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\Icd10CodeController::class, 'destroy']);
    });

    // OPD PROCEDURE
    Route::group(['prefix' => 'opd-procedure', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdProcedureController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdProcedureController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdProcedureController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\OpdProcedureController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdProcedureController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdProcedureController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdProcedureController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdProcedureController::class, 'destroy']);
    });

    // OPD INVESTIGATION ORDER
    Route::group(['prefix' => 'opd-investigation-order', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdInvestigationOrderController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdInvestigationOrderController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdInvestigationOrderController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\OpdInvestigationOrderController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdInvestigationOrderController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdInvestigationOrderController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdInvestigationOrderController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdInvestigationOrderController::class, 'destroy']);
    });

    // OPD INVESTIGATION ORDER ITEM
    Route::group(['prefix' => 'opd-investigation-order-item', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdInvestigationOrderItemController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdInvestigationOrderItemController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdInvestigationOrderItemController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\OpdInvestigationOrderItemController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdInvestigationOrderItemController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdInvestigationOrderItemController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdInvestigationOrderItemController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdInvestigationOrderItemController::class, 'destroy']);
    });

    // OPD BILL
    Route::group(['prefix' => 'opd-bill', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdBillController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdBillController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdBillController::class, 'index']);
        Route::get('/by-visit/{visitId}', [App\Http\Controllers\OpdBillController::class, 'byVisit']);
        Route::get('/{id}', [App\Http\Controllers\OpdBillController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdBillController::class, 'store']);
        Route::post('/generate/{visitId}', [App\Http\Controllers\OpdBillController::class, 'generate']);
        Route::put('/{id}', [App\Http\Controllers\OpdBillController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdBillController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdBillController::class, 'destroy']);
        Route::post('/{id}/payment', [App\Http\Controllers\OpdBillController::class, 'recordPayment']);
        Route::post('/{id}/payments', [App\Http\Controllers\OpdBillController::class, 'recordSplitPayment']);
        Route::post('/{id}/waive', [App\Http\Controllers\OpdBillController::class, 'waive']);
        Route::post('/{id}/discount', [App\Http\Controllers\OpdBillController::class, 'applyDiscount']);
        Route::post('/{id}/discount/approve', [App\Http\Controllers\OpdBillController::class, 'approveDiscount']);
        Route::post('/{id}/discount/reject', [App\Http\Controllers\OpdBillController::class, 'rejectDiscount']);
        Route::get('/{id}/print', [App\Http\Controllers\OpdBillController::class, 'print']);
        Route::get('/{id}/receipt-pdf', [App\Http\Controllers\OpdBillController::class, 'receiptPdf']);
    });

    // OPD BILL ITEM
    Route::group(['prefix' => 'opd-bill-item', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdBillItemController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdBillItemController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdBillItemController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\OpdBillItemController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdBillItemController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdBillItemController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdBillItemController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdBillItemController::class, 'destroy']);
    });

    // OPD BILL PAYMENT
    Route::group(['prefix' => 'opd-bill-payment', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\OpdBillPaymentController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\OpdBillPaymentController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\OpdBillPaymentController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\OpdBillPaymentController::class, 'show']);
        Route::post('/', [App\Http\Controllers\OpdBillPaymentController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\OpdBillPaymentController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\OpdBillPaymentController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\OpdBillPaymentController::class, 'destroy']);
    });

    // LAB TEST
    Route::group(['prefix' => 'lab-test', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\LabTestController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\LabTestController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\LabTestController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\LabTestController::class, 'show']);
        Route::post('/', [App\Http\Controllers\LabTestController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\LabTestController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\LabTestController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\LabTestController::class, 'destroy']);
    });

    // WARD
    Route::group(['prefix' => 'ward', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\WardController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\WardController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\WardController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\WardController::class, 'show']);
        Route::post('/', [App\Http\Controllers\WardController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\WardController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\WardController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\WardController::class, 'destroy']);
    });

    // BED
    Route::group(['prefix' => 'bed', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\BedController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\BedController::class, 'dropdown']);
        Route::get('/dashboard', [App\Http\Controllers\BedController::class, 'dashboard']);
        Route::get('/board', [App\Http\Controllers\BedController::class, 'board']);
        Route::get('/', [App\Http\Controllers\BedController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\BedController::class, 'show']);
        Route::post('/', [App\Http\Controllers\BedController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\BedController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\BedController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\BedController::class, 'destroy']);
    });

    // IPD ADMISSION
    Route::group(['prefix' => 'ipd-admission', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\IpdAdmissionController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\IpdAdmissionController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\IpdAdmissionController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\IpdAdmissionController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdAdmissionController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdAdmissionController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\IpdAdmissionController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\IpdAdmissionController::class, 'destroy']);
        Route::post('/{id}/transfer-bed', [App\Http\Controllers\IpdAdmissionController::class, 'transferBed']);
        Route::post('/{id}/exit', [App\Http\Controllers\IpdAdmissionController::class, 'exit']);
        Route::get('/{id}/audit-log', [App\Http\Controllers\IpdAdmissionController::class, 'auditLog']);
    });

    // IPD BILL
    Route::group(['prefix' => 'ipd-bill', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\IpdBillController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\IpdBillController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\IpdBillController::class, 'index']);
        Route::get('/by-admission/{admissionId}', [App\Http\Controllers\IpdBillController::class, 'byAdmission']);
        Route::get('/{id}', [App\Http\Controllers\IpdBillController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdBillController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdBillController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\IpdBillController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\IpdBillController::class, 'destroy']);
        Route::post('/{id}/refresh-room-charges', [App\Http\Controllers\IpdBillController::class, 'refreshRoomCharges']);
        Route::post('/{id}/item', [App\Http\Controllers\IpdBillController::class, 'addItem']);
        Route::delete('/{id}/item/{itemId}', [App\Http\Controllers\IpdBillController::class, 'removeItem']);
        Route::post('/{id}/payment', [App\Http\Controllers\IpdBillController::class, 'recordPayment']);
        Route::post('/{id}/payments', [App\Http\Controllers\IpdBillController::class, 'recordSplitPayment']);
        Route::post('/{id}/waive', [App\Http\Controllers\IpdBillController::class, 'waive']);
        Route::post('/{id}/discount', [App\Http\Controllers\IpdBillController::class, 'applyDiscount']);
        Route::post('/{id}/discount/approve', [App\Http\Controllers\IpdBillController::class, 'approveDiscount']);
        Route::post('/{id}/discount/reject', [App\Http\Controllers\IpdBillController::class, 'rejectDiscount']);
    });

    // IPD BILL ITEM
    Route::group(['prefix' => 'ipd-bill-item', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\IpdBillItemController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\IpdBillItemController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\IpdBillItemController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\IpdBillItemController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdBillItemController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdBillItemController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\IpdBillItemController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\IpdBillItemController::class, 'destroy']);
    });

    // IPD BILL PAYMENT
    Route::group(['prefix' => 'ipd-bill-payment', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\IpdBillPaymentController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\IpdBillPaymentController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\IpdBillPaymentController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\IpdBillPaymentController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdBillPaymentController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdBillPaymentController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\IpdBillPaymentController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\IpdBillPaymentController::class, 'destroy']);
    });

    // IPD ADVANCE PAYMENT
    Route::group(['prefix' => 'ipd-advance-payment', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\IpdAdvancePaymentController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\IpdAdvancePaymentController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\IpdAdvancePaymentController::class, 'index']);
        Route::get('/by-admission/{admissionId}', [App\Http\Controllers\IpdAdvancePaymentController::class, 'byAdmission']);
        Route::get('/{id}', [App\Http\Controllers\IpdAdvancePaymentController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdAdvancePaymentController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdAdvancePaymentController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\IpdAdvancePaymentController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\IpdAdvancePaymentController::class, 'destroy']);
        Route::post('/{id}/apply', [App\Http\Controllers\IpdAdvancePaymentController::class, 'apply']);
    });

    // IPD VITAL
    Route::group(['prefix' => 'ipd-vital', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\IpdVitalController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\IpdVitalController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\IpdVitalController::class, 'index']);
        Route::get('/by-admission/{admissionId}', [App\Http\Controllers\IpdVitalController::class, 'byAdmission']);
        Route::get('/{id}', [App\Http\Controllers\IpdVitalController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdVitalController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdVitalController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\IpdVitalController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\IpdVitalController::class, 'destroy']);
    });

    // IPD NURSING ASSESSMENT
    Route::group(['prefix' => 'ipd-nursing-assessment', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\IpdNursingAssessmentController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\IpdNursingAssessmentController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\IpdNursingAssessmentController::class, 'index']);
        Route::get('/by-admission/{admissionId}', [App\Http\Controllers\IpdNursingAssessmentController::class, 'byAdmission']);
        Route::get('/{id}', [App\Http\Controllers\IpdNursingAssessmentController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdNursingAssessmentController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdNursingAssessmentController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\IpdNursingAssessmentController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\IpdNursingAssessmentController::class, 'destroy']);
    });

    // IPD FLUID BALANCE
    Route::group(['prefix' => 'ipd-fluid-balance', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\IpdFluidBalanceController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\IpdFluidBalanceController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\IpdFluidBalanceController::class, 'index']);
        Route::get('/by-admission/{admissionId}', [App\Http\Controllers\IpdFluidBalanceController::class, 'byAdmission']);
        Route::get('/summary/{admissionId}', [App\Http\Controllers\IpdFluidBalanceController::class, 'summary']);
        Route::get('/{id}', [App\Http\Controllers\IpdFluidBalanceController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdFluidBalanceController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdFluidBalanceController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\IpdFluidBalanceController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\IpdFluidBalanceController::class, 'destroy']);
    });

    // IPD MEDICATION ORDER
    Route::group(['prefix' => 'ipd-medication-order', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\IpdMedicationOrderController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\IpdMedicationOrderController::class, 'dropdown']);
        Route::get('/', [App\Http\Controllers\IpdMedicationOrderController::class, 'index']);
        Route::get('/by-admission/{admissionId}', [App\Http\Controllers\IpdMedicationOrderController::class, 'byAdmission']);
        Route::get('/{id}', [App\Http\Controllers\IpdMedicationOrderController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdMedicationOrderController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdMedicationOrderController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\IpdMedicationOrderController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\IpdMedicationOrderController::class, 'destroy']);
        Route::post('/{id}/discontinue', [App\Http\Controllers\IpdMedicationOrderController::class, 'discontinue']);
        Route::post('/{id}/administer-prn', [App\Http\Controllers\IpdMedicationOrderController::class, 'administerPrn']);
    });

    // IPD MEDICATION ADMINISTRATION
    Route::group(['prefix' => 'ipd-medication-administration', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/', [App\Http\Controllers\IpdMedicationAdministrationController::class, 'index']);
        Route::get('/by-admission/{admissionId}', [App\Http\Controllers\IpdMedicationAdministrationController::class, 'byAdmission']);
        Route::get('/{id}', [App\Http\Controllers\IpdMedicationAdministrationController::class, 'show']);
        Route::post('/{id}/record', [App\Http\Controllers\IpdMedicationAdministrationController::class, 'record']);
    });

    // IPD DISCHARGE SUMMARY
    Route::group(['prefix' => 'ipd-discharge-summary', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/', [App\Http\Controllers\IpdDischargeSummaryController::class, 'index']);
        Route::get('/by-admission/{admissionId}', [App\Http\Controllers\IpdDischargeSummaryController::class, 'byAdmission']);
        Route::get('/{id}', [App\Http\Controllers\IpdDischargeSummaryController::class, 'show']);
        Route::post('/generate/{admissionId}', [App\Http\Controllers\IpdDischargeSummaryController::class, 'generate']);
        Route::put('/{id}', [App\Http\Controllers\IpdDischargeSummaryController::class, 'update']);
        Route::post('/{id}/sign', [App\Http\Controllers\IpdDischargeSummaryController::class, 'sign']);
    });

    // IPD DEATH CERTIFICATE
    Route::group(['prefix' => 'ipd-death-certificate', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/', [App\Http\Controllers\IpdDeathCertificateController::class, 'index']);
        Route::get('/by-admission/{admissionId}', [App\Http\Controllers\IpdDeathCertificateController::class, 'byAdmission']);
        Route::get('/{id}', [App\Http\Controllers\IpdDeathCertificateController::class, 'show']);
        Route::post('/', [App\Http\Controllers\IpdDeathCertificateController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\IpdDeathCertificateController::class, 'update']);
        Route::post('/{id}/certify', [App\Http\Controllers\IpdDeathCertificateController::class, 'certify']);
    });

    // ER VISIT
    Route::group(['prefix' => 'er-visit', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::post('/bulk', [App\Http\Controllers\ErVisitController::class, 'bulk']);
        Route::get('/dropdown', [App\Http\Controllers\ErVisitController::class, 'dropdown']);
        Route::get('/board', [App\Http\Controllers\ErVisitController::class, 'board']);
        Route::get('/', [App\Http\Controllers\ErVisitController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\ErVisitController::class, 'show']);
        Route::post('/', [App\Http\Controllers\ErVisitController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\ErVisitController::class, 'update']);
        Route::patch('/{id}', [App\Http\Controllers\ErVisitController::class, 'updateFields']);
        Route::delete('/{id}', [App\Http\Controllers\ErVisitController::class, 'destroy']);
        Route::post('/{id}/start-treatment', [App\Http\Controllers\ErVisitController::class, 'startTreatment']);
        Route::post('/{id}/dispose', [App\Http\Controllers\ErVisitController::class, 'dispose']);
        Route::post('/{id}/link-admission', [App\Http\Controllers\ErVisitController::class, 'linkAdmission']);
    });

    // ER TRIAGE
    Route::group(['prefix' => 'er-triage', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/', [App\Http\Controllers\ErTriageController::class, 'index']);
        Route::get('/by-visit/{erVisitId}', [App\Http\Controllers\ErTriageController::class, 'byVisit']);
        Route::get('/{id}', [App\Http\Controllers\ErTriageController::class, 'show']);
        Route::post('/', [App\Http\Controllers\ErTriageController::class, 'store']);
    });

    // PATIENT HISTORY (longitudinal EMR timeline)
    Route::group(['prefix' => 'patient-history', 'middleware' => ['restrictIp', 'authVerify']], function () {
        Route::get('/{patientId}', [App\Http\Controllers\PatientHistoryController::class, 'timeline']);
    });
});
