<?php

namespace App\Services;

use App\Models\TransportAllowance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllowanceService
{
    public function getAll()
    {
        return TransportAllowance::where('created_by', auth()->user()->id)->orderBy('id','desc')->get();
    }

    public function startJourney(Request $request)
    {
        $allowance = TransportAllowance::create([
            'from_lat'      => $request->from_lat,
            'from_lng'      => $request->from_lng,
            'from_address'  => getAddress($request->from_lat, $request->from_lng),
            'start_time'    => Carbon::now()->timezone('Asia/Dhaka'),
            'visit_type'    => $request->visit_type,
            'transport_type'=> $request->transport_type ?? null,
            'amount'        => $request->amount ?? 0.00,
            'note'          => $request->note ?? null,
            'created_by'    => auth()->user()->id,
            'client_id'      => $request->client_id,
            'follow_up_id'   => $request->follow_up_id
        ]);

        if ($request->hasFile('document')){
            saveImage($request->file('document'), 'uploads/travel_allowance/documents/', $allowance, 'document');
        }
    }

    public function endJourney(Request $request, $id)
    {
        $allowance = TransportAllowance::findOrFail($id);

        if(!is_null($allowance->end_time))
        {
            return 1;
        }

        if($allowance->created_by != auth()->user()->id)
        {
            return 2;
        }

        $allowance->update([
            'to_lat'         => $request->from_lat,
            'to_lng'         => $request->from_lng,
            'to_address'     => getAddress($request->to_lat, $request->to_lng),
            'end_time'       => Carbon::now()->timezone('Asia/Dhaka'),
            'visit_type'     => $request->visit_type,
            'transport_type' => $request->transport_type,
            'amount'         => $request->amount ?? 0.00,
            'note'           => $request->note ?? null,
            'client_id'      => $request->client_id,
            'follow_up_id'   => $request->follow_up_id
        ]);

        if ($request->hasFile('document')){
            saveImage($request->file('document'), 'uploads/travel_allowance/documents/', $allowance, 'document');
        }
    }
}
