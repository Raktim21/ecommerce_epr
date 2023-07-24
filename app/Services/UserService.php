<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll()
    {
        return User::with('roles')
            ->whereDoesntHave('employee')
            ->withSum('point_list', 'points')->paginate(10);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'details' => $request->details,
                'password' => Hash::make($request->password),
            ]);

            if($request->is_employee == 1)
            {
                $employee = Employee::create([
                    'user_id'           => $user->id,
                    'salary'            => $request->salary,
                    'general_kpi'       => $request->general_kpi,
                    'joining_date'      => $request->joining_date
                ]);

                if($request->file('document'))
                {
                    saveImage($request->file('document'), '/uploads/users/document/', $employee, 'document');
                }
            }

            $user->assignRole($request->role_id);

            if($request->file('avatar'))
            {
                saveImage($request->file('avatar'), '/uploads/users/avatar/', $user, 'avatar');
            }

            DB::commit();

            $this->sendNotification('A new user has been created.', 'user', $user->id);
            return true;
        }
        catch (QueryException $ex)
        {
            DB::rollback();
            return $ex->getMessage();
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
            if($user->avatar)
            {
                deleteFile($user->avatar);
            }
            saveImage($request->file('avatar'), '/uploads/users/avatar/', $user, 'avatar');
        }

        $employee = Employee::where('user_id', $id)->update([
            'salary'         => $request->salary,
            'general_kpi'    => $request->general_kpi,
            'joining_date'      => $request->joining_date
        ]);

        if($request->hasFile('document')) {
            if($employee->document) {
                deleteFile($employee->document);
            }
            saveImage($request->file('document'), '/uploads/users/document/', $employee, 'document');
        }

        $this->sendNotification("A user's information has been updated.", 'user', $user->id);
    }

    public function get($id)
    {
        return User::with('employee')->findOrFail($id);
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

    public function storeProfile(Request $request): void
    {
        $employee = Employee::create([
            'user_id'       => $request->user_id,
            'salary'        => $request->salary,
            'general_kpi'   => $request->general_kpi,
            'joining_date'  => $request->joining_date
        ]);

        if($request->hasFile('document')) {
            saveImage($request->file('document'), '/uploads/users/document/', $employee, 'document');
        }
    }

}
