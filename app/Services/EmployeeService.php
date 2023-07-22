<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeService
{

    public function getAll()
    {
        return User::with('employee')->withCount(['clients' => function($q) {
            $q->whereNotNull('confirmation_date');
        }])->get();
    }

    public function giveSalary(Request $request)
    {
    }
}
