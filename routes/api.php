<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\FollowUpInfoController;
use App\Http\Controllers\InterestStatusController;
use App\Http\Controllers\PaymentController;
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

    Route::controller(UserController::class)->group(function () {
        Route::get('users', 'index');
        Route::get('users/{id}', 'show');
        Route::post('users', 'store');
        Route::post('users-update/{id}', 'update');
        Route::delete('users/{id}', 'destroy');
    });

    Route::get('status', [InterestStatusController::class, 'index']);

    Route::controller(ClientsController::class)->group(function () {
        Route::get('clients', 'index');
        Route::get('clients-confirm/{id}', 'confirmClient');
        Route::get('clients/{id}', 'show');
        Route::post('clients', 'store');
        Route::put('clients-info-update/{id}', 'updateInfo');
        Route::post('clients-document-update/{id}', 'updateDoc');
        Route::delete('clients/{id}', 'destroy');
    });

    Route::controller(FollowUpInfoController::class)->group(function () {
        Route::get('clients-follow-up/{client_id}', 'show');
        Route::post('follow-up', 'store');
        Route::put('follow-up/{id}', 'update');
        Route::delete('follow-up/{id}', 'delete');
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::get('clients-payments', 'index');
        Route::post('clients-payments', 'store');
    });

});

