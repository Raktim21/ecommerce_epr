<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthInfoUpdateRequest;
use App\Http\Requests\AvatarRequest;
use App\Http\Requests\ConfirmPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        $data = Cache::remember('auth_profile'.auth()->user()->id, 60*60*24, function () {
            return $this->service->profile();
        });
        return response()->json([
            'success'   => true,
            'data'      => $data
        ]);
    }

    public function getNotifications()
    {
        Notification::where('notifiable_id', auth()->user()->id)->update([
            'send_status'   => 1
        ]);

        return response()->json([
            'success'   => true,
            'data'      => auth()->user()->notifications->whereNull('read_at'),
        ]);
    }

    public function getNewNotifications()
    {
        if (!request()->token || request()->token == null || request()->token == '' || request()->token == 'null') {
            return response()->json([
                'status'  => false,
                'error'   => 'Unauthenticated',
            ],401);
        }

        $token = request()->token;

        try {
            if( ob_get_level() > 0 ) {
                for( $i=0; $i < ob_get_level(); $i++ ) ob_flush();
                ob_end_clean();
            }

            $payload = JWTAuth::manager()->getJWTProvider()->decode($token);

            $start_time = time();

            if($payload)
            {
                $user = JWTAuth::setToken($token)->toUser();

                if($user)
                {
                    return new StreamedResponse(function () use ($start_time) {

                        echo ":" . str_repeat(" ", 2048) . "\n";
                        echo "retry: 2000\n";

                        $c = 0;
                        while ((time() - $start_time) < 30)
                        {
                            $notifications = Notification::
                            select('id','data','read_at','created_at')
                                ->where('notifiable_id', '=', auth()->user()->id)
                                ->where('send_status', '=', 0)
                                ->orderByDesc('created_at')
                                ->get();

                            foreach ($notifications as $notification)
                            {
                                $data = json_encode($notification);
                                echo "id: {$notification->id}\n";
                                echo "data: {$data}\n\n";

                                $notification->update(['send_status' => 1]);

                                if( ob_get_level() > 0 ) for( $i=0; $i < ob_get_level(); $i++ ) ob_flush();
                                flush();

                                $c++;
                                if( $c % 1000 == 0 ){
                                    gc_collect_cycles();
                                    $c=1;
                                }

                            }

                            if (connection_aborted()) {break;}
                            DB::disconnect();
                            sleep(3);
                        }

                    }, 200, [
                        'Content-Type'      => 'text/event-stream',
                        'Cache-Control'     => 'no-cache',
                        'Connection'        => 'keep-alive',
                        'X-Accel-Buffering' => 'no'
                    ]);
                }
            }
        } catch (\Throwable $th)
        {
            Log::info('notification error: ' . $th->getMessage());
        }
    }

    public function readNotification($id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();

        return response()->json([
            'success' => true,
        ]);
    }

    public function readNotifications()
    {
        auth()->user()->unreadNotifications->markAsRead();

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
