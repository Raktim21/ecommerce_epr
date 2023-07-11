<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;

class EmployeeService
{

    public function getAll()
    {
        return User::with('employee')->withCount(['clients' => function($q) {
            $q->whereNotNull('confirmation_date');
        }])->get();
    }
}
