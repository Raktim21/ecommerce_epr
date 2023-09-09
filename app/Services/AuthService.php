<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthService
{

    public function login($credentials)
    {
        if ($token = auth()->attempt($credentials))
        {
            return $token;
        }
        return null;
    }

    public function profile()
    {
        return User::with('roles')
            ->withSum('point_list','points')
            ->withCount('follow_up_reminders')
            ->find(auth()->user()->id);
    }

    public function storeAvatar(Request $request)
    {
        $user = User::find(auth()->user()->id);

        if($user->avatar)
        {
            deleteFile($user->avatar);
        }
        saveImage($request->file('avatar'), '/uploads/users/avatar/', $user, 'avatar');
    }

    public function changePassword(Request $request)
    {
        User::findOrFail(auth()->user()->id)->update([
            'password' => bcrypt($request->new_password)
        ]);
    }

    public function updateInfo(Request $request)
    {
        User::findOrFail(auth()->user()->id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'details' => $request->details,
        ]);
    }

    public function getPermissions()
    {
        $role = User::findOrFail(auth()->user()->id)->roles;

        if($role && $role[0])
        {
            return Role::find($role[0]['id'])->permissions;
        }

        return null;
    }

    public function resetPWD(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $token = Hash::make(date('isd'));
        $code = date('isd');

        $user->update([
            'password_reset_token' => $token,
            'password_reset_code'  => $code,
        ]);

//        $user->notify(new ResetPasswordNotification($user->name, $code));

        return $user->password_reset_token;
    }

    public function confirmPWD(Request $request)
    {
        $user = User::where('password_reset_token', $request->token)
            ->where('password_reset_code',$request->code)->first();

        $user->update([
            'password' => bcrypt($request->password),
            'password_reset_token' => null,
            'password_reset_code' => null,
        ]);
    }
}
