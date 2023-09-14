<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillProduct;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillService
{
    protected $bill;

    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }

    public function getBills(Request $request)
    {
        $search = $request->input('search') ?? null;

        return $this->bill->clone()
            ->when($search, function ($q) use ($search) {
                return $q->where('bill_no', 'like', "%$search%")
                    ->orWhereHas('client', function ($q1) use ($search) {
                        return $q1->where('name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    });
            })
            ->with(['client' => function ($q) {
                return $q->select('id','name','email');
            }])->with(['services.service' => function ($q) {
                return $q->select('id', 'name', 'price');
            }])
            ->latest()->paginate(15)
            ->appends($request->except('page','per_page'));
    }

    public function storeBill(Request $request)
    {
        DB::beginTransaction();

        try {
            $new_bill = $this->bill->clone()->create([
                'bill_no'   => 'BILL-'.rand(100,999).'-'.time(),
                'client_id' => $request->client_id,
                'remarks'   => $request->remarks
            ]);

            foreach ($request->services as $service)
            {
                BillProduct::create([
                    'bill_id'       => $new_bill->id,
                    'service_id'    => $service
                ]);
            }

            DB::commit();

            return $new_bill->id;
        } catch (QueryException $ex)
        {
            DB::rollback();
            return 0;
        }
    }

    public function getBill($id)
    {
        return $this->bill->clone()->with('client','services.service')->find($id);
    }
}
