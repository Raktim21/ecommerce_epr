<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionService
{

    public function roles()
    {
        return Role::all();
    }

    public function permissions()
    {
        return Permission::all();
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

    public function deleteRole($id)
    {
        if(!$this->isSuperAdmin($id))
        {
            Role::find($id)->delete();

            return true;
        }

        return false;
    }

    private function isSuperAdmin($id)
    {
        if(Role::find($id)->name == 'Super Admin')
        {
            return true;
        }
        return false;
    }

    public function createRole(Request $request)
    {
        $role = Role::create([
            'name'       => $request->role,
            'guard_name' => 'api'
        ]);

        $role->syncPermissions($request->permissions);
    }
}
