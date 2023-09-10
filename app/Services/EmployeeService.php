<?php

namespace App\Services;

use App\Models\Employee;
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
        $data = Employee::when(!auth()->user()->hasRole('Super Admin'), function($q) {
            return $q->where('user_id', auth()->user()->id);
        })
        ->whereHas('user', function ($q) {
            return $q->where('is_active', 1);
        })
        ->when(\request()->input('salary'), function ($q) use ($date) {
            return $q->where('joining_date', '<=',$date ?? Carbon::now()->subMonth());
        })
        ->with(['user' => function($q) {
            return $q->select('id','name','email','avatar','address','details')
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
            $item->kpi_payable = $item->user->clients_count == 0 ? 0 : $this->calculateKPI($item->user->clients_count);
        });

        return $data;
    }

    public function giveSalary(Request $request)
    {
        DB::beginTransaction();

        try {
            foreach($request->employees as $employee) {
                $emp = Employee::with(['user' => function($q) {
                    return $q->select('id')
                        ->withCount(['clients' => function($q) {
                            return $q->whereNotNull('confirmation_date')
                                ->whereMonth('confirmation_date', request()->input('month_id') ?? date('n'))
                                ->whereYear('confirmation_date', request()->input('year_name') ?? date('Y'));
                        }]);
                    }])->find($employee);

                $extra = $this->calculateKPI($emp->user->clients_count);

                Salary::create([
                    'employee_id'       => $employee,
                    'year_name'         => $request->year_name,
                    'month_id'          => $request->month_id,
                    'salary_payable'    => $emp->salary,
                    'kpi_payable'       => $extra,
                    'paid_amount'       => $emp->salary + $extra
                ]);
            }
            DB::commit();
            return true;
        } catch(QueryException $e) {
            DB::rollback();
            Log::info($e->getMessage());
            return false;
        }
    }

    private function calculateKPI($confirmed_clients_count)
    {
        $kpi = KPILookUp::where('client_count','<=', $confirmed_clients_count)->orderByDesc('client_count')->first();

        if(!$kpi)
        {
            return 0;
        }

        if($kpi->client_count == $confirmed_clients_count)
        {
            return $kpi->amount;
        }

        return $kpi->amount + (($confirmed_clients_count - $kpi->client_count) * $kpi->per_client_amount);
    }
}
