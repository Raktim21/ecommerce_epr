<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransportAllowanceExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = DB::table('transport_allowances')
            ->leftJoin('users','transport_allowances.created_by','=','users.id')
            ->select('transport_allowances.*','users.name as user')
            ->orderBy('transport_allowances.id', 'desc')
            ->get();

        $result = array();

        foreach ($data as $key => $datum)
        {
            $result[] = array(
                '#'             => $key + 1,
                'user'          => $datum->user,
                'from_lat'      => $datum->from_lat,
                'from_lng'          => $datum->from_lng,
                'from'          => $datum->from_address,
                'to_lat'            => $datum->to_lat,
                'to_lng'          => $datum->to_lng,
                'to'            => $datum->to_address,
                'start'         => $datum->start_time,
                'end'           => $datum->end_time,
                'visit'         => $datum->visit_type,
                'transport'     => $datum->transport_type,
                'amount'        => $datum->amount,
                'status1'       => $datum->travel_status==1 ? 'Complete' : 'Pending',
                'status2'       => $datum->allowance_status == 0 ? 'Pending' : ($datum->allowance_status == 1 ? 'Confirmed' :
                    ($datum->allowance_status == 2 ? 'Rejected' : 'Warning'))
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            '#','User','From Latitude','From Longitude','To Latitude','From Longitude',
            'Start Time','End Time','Visit Type','Transport Type','Amount',
            'Travel Status', 'Allowance Status'
        ];
    }
}
