<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll($all)
    {
        if ($all != 1){
            return User::with('roles', 'employee')
                ->withSum('point_list', 'points')->paginate(15);
        }
        return User::select('id','name','avatar')->where('is_active', 1)->get();
    }

    public function store(Request $request): bool
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
                $employee = EmployeeProfile::create([
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
            return true;
        }
        catch (QueryException $ex)
        {
            DB::rollback();
            return false;
        }
    }

    public function updateUser(Request $request, $id): bool
    {
        DB::beginTransaction();

        try {
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

            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    deleteFile($user->avatar);
                }
                saveImage($request->file('avatar'), '/uploads/users/avatar/', $user, 'avatar');
            }

            $employee = EmployeeProfile::where('user_id', $id)->first();

            if($employee) {
                $employee->update([
                    'salary'        => $request->salary ?? $employee->salary,
                    'general_kpi'   => $request->general_kpi ?? $employee->general_kpi,
                    'joining_date'  => $request->joining_date ?? $employee->joining_date
                ]);

                if ($request->hasFile('document')) {
                    if ($employee->document) {
                        deleteFile($employee->document);
                    }
                    saveImage($request->file('document'), '/uploads/users/document/', $employee, 'document');
                }
            }

            $this->sendNotification('User profile of '. $user->name .' has been updated.', '/user/'.$user->id);

            DB::commit();
            return true;
        } catch (QueryException $ex)
        {
            DB::rollback();
            return false;
        }
    }

    public function get($id)
    {
        return User::with('employee')->find($id);
    }


    public function activeStatus($id)
    {
        $user = User::findOrFail($id);

        if($user->hasRole('Super Admin') && $user->is_active == 1)
        {
            return false;
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $this->sendNotification('User account of '. $user->name .' has been'. ($user->is_active == 1 ? ' activated' : ' deactivated') , 'user');

        return true;
    }


    public function getRole($id)
    {
        return User::findOrFail($id)->roles;
    }

    public function sendNotification($message, $link): void
    {
        $users = User::role(1)->whereNot('id', auth()->user()->id)->get();

        foreach ($users as $user)
        {
            $user->notify(new AdminNotification($message, $link));
        }
    }

    public function storeProfile(Request $request): void
    {
        $employee = EmployeeProfile::updateOrCreate([
            'user_id'       => $request->user_id
        ],[
            'salary'        => $request->salary,
            'general_kpi'   => $request->general_kpi,
            'joining_date'  => $request->joining_date
        ]);

        if($request->hasFile('document')) {
            saveImage($request->file('document'), '/uploads/users/document/', $employee, 'document');
        }
    }

}
