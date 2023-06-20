<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll()
    {
        return User::with('roles')->paginate(10);
    }

    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'details' => $request->details,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role_id);

        if($request->file('avatar'))
        {
            saveImage($request->file('avatar'), '/uploads/users/avatar/', $user, 'avatar');
        }
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'details' => $request->details,
        ]);

        $user->roles()->detach();

        $user->assignRole($request->role_id);

        if ($request->hasFile('avatar'))
        {
            if(public_path($user->avatar))
            {
                deleteFile($user->avatar);
            }
            saveImage($request->file('avatar'), '/uploads/users/avatar/', $user, 'avatar');
        }
    }

    public function get($id)
    {
        return User::findOrFail($id);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);

        try {
            if(!$user->hasRole(1))
            {
                $user->delete();

                return true;
            }
            return false;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function getRole($id)
    {
        return User::findOrFail($id)->roles;
    }
}
