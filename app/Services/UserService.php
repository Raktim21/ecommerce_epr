<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll()
    {
        return User::with('roles')
            ->withSum('point_list', 'points')->paginate(10);
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

        $this->sendNotification('A new user has been created.', 'user', $user->id);
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

        $this->sendNotification("A user's information has been updated.", 'user', $user->id);
    }

    public function get($id)
    {
        return User::findOrFail($id);
    }

    public function delete($id): bool
    {
        $user = User::findOrFail($id);

        try {
            if(!$user->hasRole(1))
            {
                $user->delete();

                $this->sendNotification('A user has been deleted.', 'user', $user->id);

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

    public function sendNotification($message, $model, $id): void
    {
        $users = User::role(1)->whereNot('id',auth()->user()->id)->get();

        foreach ($users as $user)
        {
            $user->notify(new AdminNotification($message, $model, $id));
        }
    }

}
