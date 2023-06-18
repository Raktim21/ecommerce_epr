<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolePermissionService
{

    public function roles()
    {
        return Role::all();
    }

    public function assignUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->roles()->detach();

        $user->assignRole($request->role_id);
    }
}
