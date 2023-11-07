<?php

namespace App\Exports;

use App\Services\EmployeeService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeSalaryExport implements FromCollection, WithHeadings
{
    /**
    * @return Collection
    */
    public function collection()
    {
        $data = (new EmployeeService())->getAll();

        $result = [];

        foreach($data as $key => $item)
        {
            $result[] = array(
                '#'             => $key + 1,
                'name'          => $item->user->name,
                'salary'        => $item->salary,
                'kpi'           => $item->kpi_payable,
                'total'         => $item->salary + $item->kpi_payable,
                'paid'          => count($item->salary_data) == 0 ? 0 : $item->salary_data[0]->paid_amount,
                'paid_at'       => count($item->salary_data) == 0 ? 'null' : Carbon::parse($item->salary_data[0]->created_at)->format('m d, Y')
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return ['#','Name','Salary','KPI','Total Payable','Total Paid','Paid At'];
    }
}
