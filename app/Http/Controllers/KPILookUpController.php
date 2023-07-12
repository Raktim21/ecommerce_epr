<?php

namespace App\Http\Controllers;

use App\Http\Requests\KPILookUpRequest;
use App\Models\KPILookUp;
use Illuminate\Http\Request;

class KPILookUpController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => KPILookUp::all(),
        ]);
    }

    public function create(KPILookUpRequest $request)
    {
        KPILookUp::create([
            'category'          => $request->category,
            'client_count'      => $request->client_count,
            'amount'            => $request->amount,
            'per_client_amount' => $request->per_client_amount
        ]);

        return response()->json([
            'success' => true,
        ], 201);
    }
}
