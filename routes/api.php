<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\InterestStatusController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

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
        Route::get('clients/{id}', 'show');
        Route::post('clients', 'store');
        Route::post('clients-update/{id}', 'update');
        Route::delete('clients/{id}', 'destroy');
    });


});
