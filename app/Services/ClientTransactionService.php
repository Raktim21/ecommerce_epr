<?php

namespace App\Services;

use App\Imports\ClientTransactionsImport;
use App\Models\Clients;
use App\Models\ClientTransaction;
use Carbon\Carbon;
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
            ->when($request->invoice_no, function ($q) use ($request) {
                return $q->where('invoice_no', $request->invoice_no);
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

    public function getClientTransactions($client_id)
    {
        return $this->transaction->clone()
            ->leftJoin('payment_types','client_transactions.payment_type_id','=','payment_types.id')
            ->leftJoin('clients','client_transactions.client_id','=','clients.id')
            ->select('client_transactions.*','clients.name','clients.email','clients.phone_no','clients.company','clients.confirmation_date',
                'payment_types.name as payment_type')
            ->where('client_transactions.client_id', $client_id)
            ->latest('client_transactions.created_at')->get();
    }

    public function monthlyTransactionData(Request $request)
    {
        $from_date = $request->date ?? date('Y-m-d');

        $month = Carbon::parse($from_date)->format('n');

        $clients = Clients::whereNotNull('confirmation_date')->paginate(10);

        $data = [];

        foreach ($clients as $key => $client)
        {
            $data[$key] = $client;
            $data[$key]['transaction_status'] = Carbon::parse($from_date)->diffInMonths($client->confirmation_date) < 3 ? 'In Trial' :
                ($client->transactions()->whereMonth('occurred_on', $month)->exists() ? 'Paid' : 'Not Paid');

            $data[$key]['transaction_detail'] = $client->transactions()->whereMonth('occurred_on', $month)->first() ?? null;
        }

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
            ]
        ];
    }
}
