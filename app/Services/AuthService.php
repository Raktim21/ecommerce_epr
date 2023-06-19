<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

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
        return User::with('roles')->find(auth()->user()->id);
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
}
