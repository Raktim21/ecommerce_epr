<?php

namespace App\Http\Controllers;

use App\Models\Month;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MonthController extends Controller
{
    public function getAll()
    {
        $data = Cache::remember('months', 60*60*365, function () {
            return Month::all();
        });
        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }
}
