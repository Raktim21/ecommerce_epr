<?php

namespace App\Http\Controllers;

use App\Models\InterestStatus;
use Illuminate\Http\Request;

class InterestStatusController extends Controller
{
    public function index()
    {
        $data = InterestStatus::all();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
