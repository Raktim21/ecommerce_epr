<?php

namespace App\Exports;

use App\Models\Clients;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientExport implements FromCollection, WithHeadings
{
    /**
    * @return Collection
    */
    public function collection()
    {
        $data = Clients::leftJoin('payments','clients.id','=','payments.client_id')
            ->leftJoin('users','clients.added_by','=','users.id')
            ->select('clients.*','payments.id as payment_id','users.name as added_by')
            ->withCount('follow_ups')
            ->when(request()->input('confirmed') == 1 , function ($query) {
                return $query->whereNotNull('clients.confirmation_date');
            })
            ->when(request()->input('confirmed') == 0 , function ($query) {
                return $query->where('clients.confirmation_date',null);
            })
            ->when(!auth()->user()->hasRole('Super Admin'), function ($query) {
                return $query->where('clients.added_by', auth()->user()->id);
            })
            ->latest('clients.created_at')->get();

        $result = [];

        foreach($data as $key => $item)
        {
            $result[] = array(
                '#' => $key + 1,
                'company' => $item->company,
                'name' => $item->name,
                'email' => $item->email,
                'phone' => $item->phone_no,
                'area' => $item->area,
                'product' => $item->product_type,
                'interest' => $item->interest_status.'%',
                'confirmation_date' => $item->confirmation_date,
                'added_by' => $item->added_by,
                'added_on' => $item->created_at
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return ['#','Company','Client Name','Email','Phone No','Area','Product Type','Interest Rate','Confirmed On','Added By','Added On'];
    }
}
