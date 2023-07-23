<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\FoodAllowance;
use App\Models\Salary;
use App\Models\TransportAllowance;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeService
{

    public function getAll()
    {
        return Employee::with(['user' => function($q) {
                $q->select('id','name','email','phone','avatar')
                  ->withSum('allowances', 'amount');
            }])
            ->with(['salary_data' => function($q) {
                $q->where('month_id', request()->input('month_id') ?? date('n'))
                  ->whereYear('created_at', request()->input('year_name') ?? date('Y'));
            }])->get();
    }

    public function giveSalary(Request $request)
    {
        DB::beginTransaction();

        try {
            foreach($request->employees as $employee) {
                $emp = Employee::find($employee);

                $total = TransportAllowance::where('created_by', $emp->user_id)
                    ->where('allowance_status', 1)
                    ->whereRaw('year(created_at)='.$request->year_name)
                    ->whereRaw('month(created_at)='.$request->month_id)
                    ->sum('amount');
                $total += FoodAllowance::where('created_by', $emp->user_id)
                    ->where('allowance_status', 1)
                    ->whereRaw('year(created_at)='.$request->year_name)
                    ->whereRaw('month(created_at)='.$request->month_id)
                    ->sum('amount');

                Salary::create([
                    'employee_id'       => $employee,
                    'year_name'         => $request->year_name,
                    'month_id'          => $request->month_id,
                    'payable_amount'    => $emp->salary + $total,
                    'paid_amount'       => $request->pay_status == 1 ? $emp->salary + $total :
                                            ($request->pay_status == 2 ? $emp->salary : $total),
                    'incentive_paid'    => $request->pay_status != 2 ? $total : 0,
                    'pay_status'        => $request->pay_status
                ]);
            }
            DB::commit();
            return true;
        } catch(QueryException $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }
}
