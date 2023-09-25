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

    public function getAll()
    {
        return $this->transaction->clone()
            ->when(request()->input('client'), function ($q) {
                return $q->whereHas('client', function ($q1) {
                    return $q1->where('name', 'like', '%'.request()->input('client').'%');
                });
            })
            ->with(['client' => function($q) {
                return $q->select('id','name','email','phone_no','confirmation_date');
            }])
            ->with('paymentType')
            ->orderBy('client_id')
            ->latest()
            ->paginate(15);
    }
}
