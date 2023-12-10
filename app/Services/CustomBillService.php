<?php

namespace App\Services;

use App\Models\CustomBill;
use Illuminate\Http\Request;

class CustomBillService
{
    private $bill;

    public function __construct(CustomBill $bill)
    {
        $this->bill = $bill;
    }

    public function store(Request $request)
    {
        $new_bill = $this->bill->clone()->create([
            'bill_no' => 'BILL-' . rand(1000, 9999) . '-' . rand(10,99)
        ]);

        $new_bill->client()->create([
            'name'       => $request->client_name,
            'email'      => $request->client_email,
            'phone'      => $request->client_phone,
            'company'    => $request->client_company
        ]);

        $total = 0;

        foreach ($request->items as $item)
        {
            $new_bill->items()->create([
                'item'          => $item['item'],
                'quantity'      => $item['quantity'],
                'amount'        => $item['amount'],
                'total_amount'  => $item['quantity'] * $item['amount']
            ]);

            $total += $item['quantity'] * $item['amount'];
        }

        $new_bill->update([
            'total' => $total
        ]);

        return $new_bill->id;
    }

    public function getInfo($id)
    {
        return $this->bill->with('client','items')->find($id);
    }

    public function getAll(Request $request)
    {
        return $this->bill
            ->when($request->bill_no, function ($q) use ($request) {
                return $q->where('bill_no', $request->bill_no);
            })
            ->when($request->client, function ($q) use ($request) {
                return $q->whereHas('client', function ($q1) use ($request) {
                    return $q1->where('name', 'like', $request->client.'%')
                        ->orWhere('email', 'like', $request->client.'%');
                });
            })
            ->with('client','items')
            ->latest()->paginate(15)
            ->appends($request->except('page','per_page'));
    }
}
