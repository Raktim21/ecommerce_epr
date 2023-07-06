<?php

namespace App\Services;

use App\Models\FoodAllowance;
use App\Models\TransportAllowance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllowanceService
{
    public function getAllTransportAllowance()
    {
        if(auth()->user()->hasRole('Super Admin'))
        {
            return TransportAllowance::with('created_by_info','client','follow_up')->latest()->paginate(request()->input('per_page') ?? 10);
        }
        return TransportAllowance::with('client','follow_up')->where('created_by', auth()->user()->id)->orderBy('id','desc')
            ->paginate(request()->input('per_page') ?? 10);
    }

    public function getAllFoodAllowance()
    {
        if(auth()->user()->hasRole('Super Admin'))
        {
            return FoodAllowance::with('created_by_info','client','follow_up')->latest()->get();
        }
        return FoodAllowance::with('client','follow_up')->where('created_by', auth()->user()->id)->orderBy('id','desc')->get();
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

    public function updateFoodStatus(Request $request, $id)
    {
        FoodAllowance::findOrFail($id)->update([
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

    public function currentTransport()
    {
        return TransportAllowance::with('client','follow_up')->where('created_by',auth()->user()->id)->where('travel_status',0)->first();
    }

    public function createFoodAllowance(Request $request): void
    {
        $allowance = FoodAllowance::create([
            'lat'           => $request->lat,
            'lng'           => $request->lng,
            'address'       => getAddress($request->lat, $request->lng),
            'amount'        => $request->amount,
            'note'          => $request->note,
            'occurred_on'   => Carbon::now()->timezone('Asia/Dhaka'),
            'created_by'    => auth()->user()->id,
            'client_id'     => $request->client_id,
            'follow_up_id'  => $request->follow_up_id
        ]);

        if ($request->hasFile('document')){
            saveImage($request->file('document'), 'uploads/food_allowance/documents/', $allowance, 'document');
        }
    }

    public function deleteFoodAllowance($id): bool
    {
        $food = FoodAllowance::findOrFail($id);

        if($food->allowance_status != 0)
        {
            return false;
        }

        $food->delete();

        return true;
    }

    public function getTransportAllowance($id)
    {
        return TransportAllowance::with('created_by_info','client','follow_up')->findOrFail($id);
    }

    public function getFoodAllowance($id)
    {
        return FoodAllowance::with('created_by_info','client','follow_up')->findOrFail($id);
    }

    public function getTransportSearchResult(Request $request)
    {
        $search             = $request->search;
        $start_date         = $request->start_date;
        $end_date           = $request->end_date;
        $amount_start_range = $request->amount_start_range;
        $amount_end_range   = $request->amount_end_range;

        if($start_date && is_null($end_date))
        {
            $end_date = date('Y-m-d');
        }

        if($amount_end_range && is_null($amount_start_range))
        {
            $amount_start_range = 0;
        }

        return DB::table('transport_allowances')
            ->leftJoin('users','transport_allowances.created_by','=','users.id')
            ->when($start_date!=null, function($query) use($start_date, $end_date) {
                return $query->whereBetween('transport_allowances.created_at',[$start_date, $end_date]);
            })
            ->when($search!=null, function($query) use($search) {
                return $query->where('users.name','like',"%$search%");
            })
            ->when($amount_end_range!=null, function ($query) use($amount_start_range, $amount_end_range) {
                return $query->whereBetween('amount',[$amount_start_range,$amount_end_range]);
            })
            ->select('transport_allowances.*','users.name')
            ->orderBy('transport_allowances.id', 'desc')
            ->paginate(request()->input('per_page') ?? 10);
    }
}
