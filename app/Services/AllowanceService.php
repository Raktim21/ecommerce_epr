<?php

namespace App\Services;

use App\Models\AllowancePayslip;
use App\Models\FoodAllowance;
use App\Models\TransportAllowance;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllowanceService
{
    public function startJourney(Request $request): bool
    {
        if(TransportAllowance::where('created_by',auth()->user()->id)->whereNull('end_time')->exists())
        {
            return false;
        }
        $allowance = TransportAllowance::create([
            'from_lat'       => $request->from_lat,
            'from_lng'       => $request->from_lng,
            'from_address'   => $request->from_address,
            'to_address'     => $request->to_address,
            'start_time'     => Carbon::now()->timezone('Asia/Dhaka'),
            'visit_type'     => $request->visit_type,
            'transport_type' => $request->transport_type ?? null,
            'amount'         => $request->amount ?? 0.00,
            'note'           => $request->note ?? null,
            'created_by'     => auth()->user()->id,
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

        if(!is_null($allowance->end_time))
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
            'from_address'   => $request->from_address,
            'to_address'     => $request->to_address,
            'end_time'       => Carbon::now()->timezone('Asia/Dhaka'),
            'visit_type'     => $request->visit_type,
            'transport_type' => $request->transport_type,
            'amount'         => $request->amount,
            'note'           => $request->note ?? null,
            'client_id'      => $request->client_id ?? null,
            'follow_up_id'   => $request->follow_up_id ?? null,
            'updated_at'     => Carbon::now()->timezone('Asia/Dhaka')
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

    public function updateStatus(Request $request): bool
    {
        DB::beginTransaction();

        try {
            foreach ($request->allowances as $id) {
                $allowance = TransportAllowance::find($id);

                $allowance->update([
                    'allowance_status' => $request->status
                ]);
            }

            DB::commit();

            return true;
        } catch (QueryException $ex)
        {
            DB::rollback();
            return false;
        }
    }

    public function updateFoodStatus(Request $request): bool
    {
        DB::beginTransaction();

        try {
            foreach($request->allowances as $id) {
                $allowance = FoodAllowance::find($id);

                $allowance->update([
                    'allowance_status' => $request->status
                ]);
            }

            DB::commit();
            return true;
        }
        catch (QueryException $ex)
        {
            DB::rollback();
            return false;
        }
    }

    public function updateInfo(Request $request, $id): bool
    {
        $allowance = TransportAllowance::findOrFail($id);

        if($allowance->created_by != auth()->user()->id || $allowance->end_time)
        {
            return false;
        }
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

        return true;
    }

    public function currentTransport()
    {
        return TransportAllowance::with('client','follow_up')
            ->where('created_by', auth()->user()->id)
            ->whereNull('end_time')->first();
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

        if($food->allowance_status != 0 || $food->created_by != auth()->user()->id)
        {
            return false;
        }

        $food->delete();

        (new UserService)->sendNotification('Status of a food allowance has been changed.', 'food-allowance', $food->id);

        return true;
    }

    public function getTransportAllowance($id)
    {
        $data = TransportAllowance::with('created_by_info','client','follow_up')->find($id);

        if(!$data)
        {
            return null;
        }
        return !auth()->user()->hasRole('Super Admin') && $data->created_by != auth()->user()->id ? null : $data;
    }

    public function getFoodAllowance($id)
    {
        return FoodAllowance::with('created_by_info','client','follow_up')->findOrFail($id);
    }

    public function getTransportSearchResult(Request $request)
    {
        $search             = $request->search;
        $status             = $request->status;
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
            ->when(!auth()->user()->hasRole('Super Admin'), function($query) {
                return $query->where('transport_allowances.created_by', auth()->user()->id);
            })
            ->when($start_date!=null, function($query) use($start_date, $end_date) {
                return $query->whereBetween('transport_allowances.created_at',[$start_date, date('Y-m-d', strtotime($end_date . '+1 day'))]);
            })
            ->when($search!=null, function($query) use($search) {
                return $query->where('users.name','like',"%$search%");
            })
            ->when($status!=null, function($query) use($status) {
                return $query->where('transport_allowances.allowance_status',$status);
            })
            ->when($amount_end_range!=null, function ($query) use($amount_start_range, $amount_end_range) {
                return $query->whereBetween('amount',[$amount_start_range,$amount_end_range]);
            })
            ->leftJoin('clients','transport_allowances.client_id','=','clients.id')
            ->select('transport_allowances.*','users.name','clients.name as client_name')
            ->orderBy('transport_allowances.id', 'desc')
            ->paginate(request()->input('per_page') ?? 10)->appends($request->except('page','per_page'));
    }

    public function getFoodSearchResult(Request $request)
    {
        $search             = $request->search;
        $status             = $request->status;
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

        return DB::table('food_allowances')
            ->leftJoin('users','food_allowances.created_by','=','users.id')
            ->when(!auth()->user()->hasRole('Super Admin'), function($query) {
                return $query->where('food_allowances.created_by', auth()->user()->id);
            })
            ->when($start_date!=null, function($query) use($start_date, $end_date) {
                return $query->whereBetween('food_allowances.created_at',[$start_date, $end_date]);
            })
            ->when($search!=null, function($query) use($search) {
                return $query->where('users.name','like',"%$search%");
            })
            ->when($status!=null, function($query) use($status) {
                return $query->where('food_allowances.allowance_status',$status);
            })
            ->when($amount_end_range!=null, function ($query) use($amount_start_range, $amount_end_range) {
                return $query->whereBetween('amount',[$amount_start_range,$amount_end_range]);
            })
            ->select('food_allowances.*','users.name')
            ->orderBy('food_allowances.id', 'desc')
            ->paginate(request()->input('per_page') ?? 10);
    }

    public function invoices()
    {
        return AllowancePayslip::when(\request()->input('payslip_no'), function ($q) {
            return $q->where('payslip_no', \request()->input('payslip_no'));
        })->latest()->paginate(10);
    }
}
