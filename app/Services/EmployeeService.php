<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\FoodAllowance;
use App\Models\KPILookUp;
use App\Models\Salary;
use App\Models\TransportAllowance;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeService
{
    public function getAll()
    {
        $date = null;
        if(request()->input('month_id') && request()->input('year_name'))
        {
            $date = Carbon::create(request()->input('year_name'), request()->input('month_id'), 1);
        }
        $data = EmployeeProfile::when(!auth()->user()->hasRole('Super Admin'), function($q) {
            return $q->where('user_id', auth()->user()->id);
        })
        ->when(\request()->input('is_active') == 1, function ($q) use ($date) {
            return $q->whereHas('user', function ($q) {
                return $q->where('is_active', 1);
            });
        })
        ->when(\request()->input('salary'), function ($q) use ($date) {
            return $q->where('joining_date', '<=',$date ?? Carbon::now()->subMonth());
        })
        ->with(['user' => function($q) {
            return $q->select('id','name','email','avatar','address','details','is_active')
                ->withCount(['clients' => function($q) {
                    return $q->whereNotNull('confirmation_date')
                        ->whereMonth('confirmation_date', request()->input('month_id') ?? date('n'))
                        ->whereYear('confirmation_date', request()->input('year_name') ?? date('Y'));
                }]);
        }])
        ->with(['salary_data' => function($q) {
            $q->where('month_id', request()->input('month_id') ?? date('n'))
              ->whereYear('created_at', request()->input('year_name') ?? date('Y'));
        }])
        ->get();

        $data->map(function ($item) {
            $item->kpi_payable = $item->user->clients_count <= $item->general_kpi ? 0 : $this->calculateKPI($item->general_kpi, $item->user->clients_count);
        });

        return $data;
    }

    public function giveSalary(Request $request)
    {
        DB::beginTransaction();

        try {
            foreach($request->employees as $employee)
            {
                $emp = EmployeeProfile::with(['user' => function($q) {
                    return $q->select('id')
                        ->withCount(['clients' => function($q) {
                            return $q->whereNotNull('confirmation_date')
                                ->whereMonth('confirmation_date', request()->input('month_id'))
                                ->whereYear('confirmation_date', request()->input('year_name'));
                        }]);
                    }])->find($employee['id']);

                $extra = $this->calculateKPI($emp->general_kpi, $emp->user->clients_count);

                Salary::create([
                    'employee_id'       => $employee['id'],
                    'year_name'         => $request->year_name,
                    'month_id'          => $request->month_id,
                    'salary_payable'    => $emp->salary,
                    'kpi_payable'       => $extra,
                    'paid_amount'       => $employee['amount'],
                    'remarks'           => $request->remarks
                ]);
            }
            DB::commit();
            return true;
        } catch(QueryException $e) {
            DB::rollback();
            return false;
        }
    }

    private function calculateKPI($base_kpi, $confirmed_clients_count)
    {
        $bonus_kpi = $confirmed_clients_count - $base_kpi;

        $kpi = KPILookUp::where('client_count','<=', $bonus_kpi)->orderByDesc('client_count')->first();

        if(!$kpi)
        {
            return 0;
        }

        return $kpi->client_count == $bonus_kpi ? $kpi->amount :
            $kpi->amount + (($bonus_kpi - $kpi->client_count) * $kpi->per_client_amount);
    }
}
