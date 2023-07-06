<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FoodAllowanceExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = DB::table('food_allowances')
            ->leftJoin('users','food_allowances.created_by','=','users.id')
            ->select('food_allowances.*','users.name as user')
            ->orderBy('food_allowances.id', 'desc')
            ->get();

        $result = array();

        foreach ($data as $key => $datum) {
            $result[] = array(
                '#'             => $key + 1,
                'user'          => $datum->user,
                'from_lat'      => $datum->lat,
                'from_lng'      => $datum->lng,
                'address'       => $datum->address,
                'amount'        => $datum->amount,
                'time'          => $datum->occurred_on,
                'status2'       => $datum->allowance_status == 0 ? 'Pending' : ($datum->allowance_status == 1 ? 'Confirmed' :
                    ($datum->allowance_status == 2 ? 'Rejected' : 'Warning'))
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return ['#','User','Latitude','Longitude','Address','Amount','Occurred On','Allowance Status'];
    }
}
