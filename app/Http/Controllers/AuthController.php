<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthInfoUpdateRequest;
use App\Http\Requests\AvatarRequest;
use App\Http\Requests\ConfirmPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends Controller
{
    private $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function login(LoginRequest $request)
    {
        if($token = $this->service->login($request->only('email', 'password')))
        {
            return response()->json([
                'success' => true,
                'admin_access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        }
        else
        {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 401);
        }
    }


    public function profile()
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->profile()
        ]);
    }

    public function updateAvatar(AvatarRequest $request)
    {
        $this->service->storeAvatar($request);

        return response()->json([
            'success' => true,
        ]);
    }


    public function updateInfo(AuthInfoUpdateRequest $request)
    {
        $this->service->updateInfo($request);

        return response()->json([
            'success' => true,
        ]);
    }


    public function updatePassword(PasswordRequest $request)
    {
        $this->service->changePassword($request);

        return response()->json([
            'success' => true,
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $token = $this->service->resetPWD($request);

        return response()->json([
            'success'       => true,
            'reset_token'   => $token
        ]);
    }

    public function confirmPassword(ConfirmPasswordRequest $request)
    {
        $this->service->confirmPWD($request);

        return response()->json([
            'success' => true,
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'success' => true
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'admin_access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function getPermissions()
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getPermissions(),
        ]);
    }
}
