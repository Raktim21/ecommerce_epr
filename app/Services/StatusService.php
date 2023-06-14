<?php

namespace App\Services;

use App\Models\InterestStatus;

class StatusService
{
    public function getAll()
    {
        return InterestStatus::all();
    }

}
