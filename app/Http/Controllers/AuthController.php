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
        $credentials = array(
            (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone') => $request->get('email'),
            'password' => $request->get('password')
        );

        if (User::where('email',$request->email)->first()->is_active != 1) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 401);
        }

        if($token = $this->service->login($credentials))
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

    public function getNotifications()
    {
        return response()->json([
            'success'   => true,
            'data'      => auth()->user()->notifications->whereNull('read_at'),
        ]);
    }

    public function readNotification($id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();

        return response()->json([
            'success' => true,
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
