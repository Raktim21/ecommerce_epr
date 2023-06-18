<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function assignMultipleUser(Request $request)
    {
        DB::beginTransaction();

        try {
            foreach($request->users as $value)
            {
                $user = User::findOrFail($value);

                $user->roles()->detach();

                $user->assignRole($request->role_id);
            }
            DB::commit();

            return true;
        }
        catch (QueryException $ex)
        {
            DB::rollback();

            return false;
        }
    }

    public function role($id)
    {
        return Role::with('permissions')->findOrFail($id);
    }
}
