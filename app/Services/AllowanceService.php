<?php

namespace App\Services;

use App\Models\TransportAllowance;

class AllowanceService
{
    public function getAll()
    {
        return TransportAllowance::where('created_by', auth()->user()->id)->orderBy('id','desc')->get();
    }
}
