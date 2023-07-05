<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\FollowUpInfoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPointController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('reset-password', 'resetPassword');
    Route::post('confirm-password', 'confirmPassword');
});

Route::group(['middleware' => ['jwt.auth']], function () {

    Route::controller(AuthController::class)->group(function () {
        Route::get('logout', 'logout');
        Route::get('refresh', 'refresh');

        Route::get('notifications', 'getNotifications');
        Route::get('notifications/read/{id}', 'readNotification');

        Route::get('profile', 'profile');
        Route::post('update-avatar', 'updateAvatar');
        Route::put('update-info', 'updateInfo');
        Route::put('update-password', 'updatePassword');
        Route::get('auth-permissions', 'getPermissions');
    });

    Route::controller(RolePermissionController::class)->group(function () {
        Route::get('roles', 'roleList')->middleware(['permission:get-role-list']);
        Route::get('roles/{id}', 'getRole')->middleware(['permission:get-role-info']);
        Route::post('roles', 'createRole')->middleware(['permission:create-role']);
        Route::put('roles/{id}', 'updateRole')->middleware(['permission:update-role']);
        Route::delete('roles/{id}', 'deleteRole')->middleware(['permission:delete-role']);
        Route::get('permissions', 'permissionList')->middleware(['permission:get-permission-list']);
        Route::post('assign-role/{user_id}', 'assignRole')->middleware('permission:assign-role-to-user');
        Route::post('users-assign-role', 'assignUsers')->middleware('permission:assign-role-to-user');
    });

    Route::controller(UserPointController::class)->group(function () {
        Route::get('point-types', 'getList')->middleware(['permission:get-point-type-list']);
        Route::put('point-types/{id}', 'updatePoint')->middleware(['permission:update-point-type']);
        Route::get('user/points/{user_id}', 'pointData');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('users', 'index')->middleware('permission:get-user-list');
        Route::get('users/{id}', 'show')->middleware('permission:get-user-info');
        Route::post('users', 'store')->middleware('permission:create-user');
        Route::post('users-update/{id}', 'update')->middleware('permission:update-user');
        Route::delete('users/{id}', 'destroy')->middleware('permission:delete-user');
    });

    Route::controller(ClientsController::class)->group(function () {
        Route::get('clients', 'index')->middleware('permission:get-client-list');
        Route::get('get-unpaid-clients', 'unpaidClients')->middleware('permission:get-unpaid-client-list');
        Route::get('clients/{id}', 'show')->middleware('permission:get-client-info');
        Route::post('clients', 'store')->middleware('permission:create-client');
        Route::post('import/clients', 'importClients')->middleware('permission:import-client');
        Route::put('clients-info-update/{id}', 'updateInfo')->middleware('permission:update-client');
        Route::post('clients-document-update/{id}', 'updateDoc')->middleware('permission:update-client');
        Route::post('clients/delete', 'destroy')->middleware('permission:delete-client');
    });

    Route::controller(FollowUpInfoController::class)->group(function () {
        Route::get('clients-follow-up/{client_id}', 'show')->middleware('permission:get-client-follow-up');
        Route::post('follow-up', 'store')->middleware('permission:create-client-follow-up');
        Route::put('follow-up/{id}', 'update')->middleware('permission:update-client-follow-up');
        Route::delete('follow-up/{id}', 'delete')->middleware('permission:delete-client-follow-up');
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::get('clients-payments', 'index')->middleware('permission:get-client-payment');
        Route::post('clients-payments', 'store')->middleware('permission:create-client-payment');
        Route::get('payment_types', 'getTypes')->middleware('permission:get-payment-type');
        Route::get('client-payment-data', 'getPayData')->middleware('permission:get-client-payment');
        Route::get('payslip/{id}', 'getPayslip')->middleware('permission:get-payslip');

        Route::get('payment_categories', 'getCategories')->middleware('permission:get-payment-category');
        Route::post('payment_categories', 'storeCategories')->middleware('permission:create-payment-category');
        Route::delete('payment_categories/{id}', 'deleteCategories')->middleware('permission:delete-payment-category');
    });

    Route::controller(WebsiteController::class)->group(function () {
        Route::get('websites', 'index')->middleware('permission:get-website');
        Route::post('websites', 'store')->middleware('permission:create-website');
    });

    Route::controller(AllowanceController::class)->group(function () {
        Route::get('transport-allowances', 'transportAllowanceList');
        Route::get('transport-allowances/export/all', 'transportAllowanceExport');
        Route::get('transport-allowances/get/{id}', 'transportAllowance');
        Route::get('transport-allowances/current', 'currentTransportAllowance');
        Route::post('transport-allowances/start', 'start');
        Route::post('transport-allowances/end/{id}', 'end');
        Route::put('transport-allowances/update/{id}', 'update');
        Route::put('transport-allowances/change-status/{id}', 'changeStatus')->middleware('permission:change-transport-allowance-status');

        Route::get('food-allowances', 'foodAllowanceList');
        Route::post('food-allowances', 'foodAllowanceStore');
        Route::put('food-allowances/update-status/{id}', 'foodAllowanceUpdate')->middleware('permission:change-food-allowance-status');
        Route::delete('food-allowances/delete/{id}', 'foodAllowanceDelete');
    });

});
