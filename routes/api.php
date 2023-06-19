<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\FollowUpInfoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => ['jwt.auth']], function () {

    Route::controller(AuthController::class)->group(function () {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('refresh', [AuthController::class, 'refresh']);

        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('update-avatar', [AuthController::class, 'updateAvatar']);
        Route::put('update-info', [AuthController::class, 'updateInfo']);
        Route::put('update-password', [AuthController::class, 'updatePassword']);
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
    });

});
