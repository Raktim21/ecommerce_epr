<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\Payment;
use App\Models\PaymentType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function getAll()
    {
        return Payment::leftJoin('clients','payments.client_id','=','clients.id')
            ->leftJoin('payment_types', 'payments.payment_type_id','=','payment_types.id')
            ->select('payments.*','clients.id as client_id','clients.name as client_name','payment_types.name as payment_type')
            ->paginate(10);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'client_id' => $request->client_id,
                'payment_type_id' => $request->payment_type_id,
                'transaction_id' => $request->transaction_id ?? 'N/A',
                'invoice_no' => 'PAY-'.rand(100,999).'-'.time(),
                'amount' => $request->amount
            ]);

            Clients::find($request->client_id)->update([
                'confirmation_date' => Carbon::now(),
            ]);

            DB::commit();

            return $payment->id;
        }
        catch (\Exception $e)
        {
            DB::rollback();

            return 0;
        }
    }

    public function getAllTypes()
    {
        return PaymentType::all();
    }

    public function read($id)
    {
        return Payment::with('client','type')->findOrFail($id);
    }

}
