<?php

namespace App\Http\Controllers;

use App\Models\Month;
use Illuminate\Support\Facades\Cache;

class MonthController extends Controller
{
    public function getAll()
    {
        $data = Cache::rememberForever('months', function () {
            return Month::orderBy('id')->get();
        });
        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }
}
