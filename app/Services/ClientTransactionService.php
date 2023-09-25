<?php

namespace App\Services;

use App\Imports\ClientTransactionsImport;
use App\Models\ClientTransaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ClientTransactionService
{
    protected $transaction;

    public function __construct(ClientTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function storeTransaction(Request $request): void
    {
        $this->transaction->clone()->create([
            'client_id'         => $request->client_id,
            'invoice_no'        => 'TRX-'.rand(100,999).'-'.time(),
            'payment_type_id'   => $request->payment_type_id,
            'transaction_id'    => $request->transaction_id,
            'amount'            => $request->amount,
            'occurred_on'       => $request->occurred_on,
            'remarks'           => $request->remarks
        ]);
    }

    public function importTransactions(Request $request)
    {
        $file = $request->file('file');

        try {
            Excel::import(new ClientTransactionsImport, $file);

            return true;
        }
        catch (\Exception $ex)
        {
            return false;
        }
    }

    public function getAll(Request $request)
    {
        return $this->transaction->clone()
            ->with(['client' => function($q) {
                return $q->select('id','name','email','phone_no','confirmation_date');
            }])
            ->when($request->client, function ($q) use ($request) {
                return $q->whereHas('client', function ($q1) use ($request) {
                    return $q1->where('name', 'like', '%'.$request->client.'%');
                });
            })
            ->when($request->start_date, function ($q) use ($request) {
                return $q->whereBetween('occurred_on', [$request->start_date, $request->end_date ?? date('Y-m-d')]);
            })
            ->when($request->trx_id, function ($q) use ($request) {
                return $q->where('transaction_id', $request->trx_id);
            })
            ->with('paymentType')
            ->orderBy('client_id')
            ->latest()
            ->paginate(15);
    }
}
