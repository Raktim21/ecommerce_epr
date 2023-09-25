<?php

namespace App\Exports;

use App\Models\ClientTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientTransactionsExport implements FromCollection, WithHeadings
{
    /**
    * @return Collection
    */
    public function collection()
    {
        $data = ClientTransaction::leftJoin('payment_types','client_transactions.payment_type_id','=','payment_types.id')
            ->leftJoin('clients','client_transactions.client_id','=','clients.id')
            ->select('client_transactions.*','clients.*','payment_types.name as payment_type')
            ->when(request()->input('client_id'), function ($query) {
                return $query->where('client_transactions.client_id', request()->input('client_id'));
            })
            ->latest('client_transactions.created_at')->get();

        $result = [];

        foreach($data as $key => $item)
        {
            $result[] = array(
                '#' => $key + 1,
                'name' => $item->name,
                'email' => $item->email,
                'phone' => $item->phone_no,
                'amount' => $item->amount,
                'type' => $item->payment_type,
                'trx_id' => $item->transaction_id,
                'time' => $item->occurred_on,
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return ['#','Client Name','Email','Phone No','Amount','Payment Type','Transaction ID','Date'];
    }
}
