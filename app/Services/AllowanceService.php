<?php

namespace App\Services;

use App\Models\TransportAllowance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AllowanceService
{
    public function getAllTransportAllowance()
    {
        if(auth()->user()->hasRole('Super Admin'))
        {
            return TransportAllowance::with('created_by_info','client','follow_up')->latest()->get();
        }
        return TransportAllowance::with('client','follow_up')->where('created_by', auth()->user()->id)->orderBy('id','desc')->get();
    }

    public function getAllFoodAllowance()
    {
        if(auth()->user()->hasRole('Super Admin'))
        {
            return TransportAllowance::with('created_by_info','client','follow_up')->latest()->get();
        }
        return TransportAllowance::with('client','follow_up')->where('created_by', auth()->user()->id)->orderBy('id','desc')->get();
    }

    public function startJourney(Request $request): bool
    {
        if(TransportAllowance::where('created_by',auth()->user()->id)->where('travel_status',0)->exists())
        {
            return false;
        }
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

        return true;
    }

    public function endJourney(Request $request, $id): int
    {
        $allowance = TransportAllowance::findOrFail($id);

        if(!is_null($allowance->end_time) && $allowance->travel_status == 1)
        {
            return 1;
        }

        if($allowance->created_by != auth()->user()->id)
        {
            return 2;
        }

        if(is_null($allowance->document) && !$request->hasFile('document'))
        {
            return 3;
        }

        $allowance->update([
            'to_lat'         => $request->to_lat,
            'to_lng'         => $request->to_lng,
            'to_address'     => getAddress($request->to_lat, $request->to_lng),
            'end_time'       => Carbon::now()->timezone('Asia/Dhaka'),
            'visit_type'     => $request->visit_type,
            'transport_type' => $request->transport_type,
            'amount'         => $request->amount,
            'note'           => $request->note ?? null,
            'client_id'      => $request->client_id ?? null,
            'follow_up_id'   => $request->follow_up_id ?? null,
            'travel_status'  => 1
        ]);

        if ($request->hasFile('document'))
        {
            if($allowance->document)
            {
                deleteFile($allowance->document);
            }
            saveImage($request->file('document'), 'uploads/travel_allowance/documents/', $allowance, 'document');
        }

        return 0;
    }

    public function updateStatus(Request $request, $id): void
    {
        TransportAllowance::findOrFail($id)->update([
            'allowance_status' => $request->allowance_status
        ]);
    }

    public function updateInfo(Request $request, $id): void
    {
        $allowance = TransportAllowance::findOrFail($id);

        $allowance->update([
            'note' => $request->note ?? $allowance->note,
        ]);

        if ($request->hasFile('document'))
        {
            if($allowance->document)
            {
                deleteFile($allowance->document);
            }
            saveImage($request->file('document'), 'uploads/travel_allowance/documents/', $allowance, 'document');
        }
    }


}
