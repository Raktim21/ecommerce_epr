<?php

namespace App\Services;

use App\Models\KPILookUp;
use Illuminate\Http\Request;

class KPIService
{
    public function store(Request $request)
    {
        KPILookUp::create([
            'client_count'      => $request->client_count,
            'amount'            => $request->amount,
            'per_client_amount' => $request->per_client_amount
        ]);
    }

    public function getAll()
    {
        return KPILookUp::orderBy('id')->get();
    }

    public function updateLookUp(Request $request, $id)
    {
        KPILookUp::findOrFail($id)->update([
            'client_count'      => $request->client_count,
            'amount'            => $request->amount,
            'per_client_amount' => $request->per_client_amount
        ]);
    }

    public function deleteKpi($id)
    {
        KPILookUp::findOrFail($id)->delete();
    }

}
