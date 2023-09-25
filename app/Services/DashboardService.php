<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\EmployeeProfile;
use App\Models\FollowUpReminder;
use App\Models\FoodAllowance;
use App\Models\Service;
use App\Models\TransportAllowance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    private $employee, $clients;

    public function __construct(EmployeeProfile $employee, Clients $clients)
    {
        $this->employee = $employee;
        $this->clients  = $clients;
    }

    public function adminDashboard()
    {
        $clients               = $this->clients->whereNull('confirmation_date');
        $confirmed_clients     = $this->clients->whereNotNull('confirmation_date');
        $pending_follow_up     = $this->getPendingFollowUps();

        $monthly_client_data   = [];

        $startDate = Carbon::now()->subMonth(12);
        $endDate   = Carbon::now();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMonth()) {
            $monthly_client_data[]   = array(
                'clients'           => $clients->clone()->when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                                            return $q->where('added_by', auth()->user()->id);
                                        })->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])
                                        ->count(),

                'confirmed_clients' => $confirmed_clients->clone()->when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                                            return $q->where('added_by', auth()->user()->id);
                                        })->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])
                                        ->count(),
                'month'             => $date->copy()->format('F, Y')
            );
        }

        return [
            'total_employees'               => $this->employee->clone()->whereHas('user', function ($q) {
                                                    return $q->where('is_active', 1);
                                                })->count(),
            'total_client'                  => $clients->clone()
                                                ->when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                                                    return $q->where('added_by', auth()->user()->id);
                                                })->count(),

            'total_confirmed_client'        => $confirmed_clients->clone()
                                                ->when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                                                    return $q->where('added_by', auth()->user()->id);
                                                })->count(),

            'services'                      => Service::where('status', 1)->count(),
            'star_employee'                 => $this->starEmployee(),
            'pending_followups'             => $pending_follow_up,
            'monthly_client_data'           => $monthly_client_data,
            'user_point_report'             => $this->userPointReport(),
            'employee_kpi'                  => $this->employeeKPI(),
            'allowances'                    => $this->getAllowanceData(),
//            'client_transactions'           => auth()->user()->hasRole('Super Admin') ? $this->transactionData() : null,
        ];
    }

    private function userPointReport()
    {
        return DB::table('user_points')
            ->leftJoin('users', 'user_points.user_id','=','users.id')
            ->selectRaw('SUM(points) as points_sum,user_id,users.name as username')
            ->groupBy('user_id','username')
            ->orderByDesc('points_sum')
            ->get();
    }

    private function employeeKPI()
    {
        return EmployeeProfile::select('id','user_id')
            ->with(['user' => function($q) {
            return $q->select('id','name')
                ->withCount(['clients' => function($q) {
                    return $q->whereNotNull('confirmation_date');
                }]);
        }])->get();
    }

    private function starEmployee()
    {
        $startDate = Carbon::now()->subMonth(3);

        return array(
            'month' => Carbon::now()->subMonth(3)->format('F, Y'),
            'data' => DB::table('employee_profiles')
            ->leftJoin('users','employee_profiles.user_id','=','users.id')
            ->join('clients','users.id','=','clients.added_by')
            ->whereNotNull('confirmation_date')
            ->whereMonth('confirmation_date', Carbon::parse($startDate)->format('n'))
            ->whereYear('confirmation_date', Carbon::parse($startDate)->format('Y'))
            ->selectRaw('count(clients.id) as clients_count, users.name as username, users.id as user_id')
            ->groupBy('users.id','users.name')
            ->orderByDesc('clients_count')
            ->first()
        );
    }

    private function getPendingFollowUps()
    {
        return FollowUpReminder::when(!auth()->user()->hasRole('Super Admin'), function ($q) {
            return $q->where('added_by', auth()->user()->id);
        })->where('followup_session', '>', date('Y-m-d H:i:s'))
            ->count();
    }

    private function getAllowanceData()
    {
        $startDate = Carbon::now()->subMonth(12);
        $endDate   = Carbon::now();

        $data = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMonth()) {
            $data[] = array(
                'paid_allowance_total' => TransportAllowance::when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                        return $q->where('created_by', auth()->user()->id);
                    })->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])
                        ->where('allowance_status', 1)
                        ->sum('amount') + FoodAllowance::when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                        return $q->where('created_by', auth()->user()->id);
                    })->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])
                        ->where('allowance_status', 1)
                        ->sum('amount'),
                'unpaid_allowance_total' => TransportAllowance::when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                        return $q->where('created_by', auth()->user()->id);
                    })->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])
                        ->whereNot('allowance_status', 1)
                        ->sum('amount') + FoodAllowance::when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                        return $q->where('created_by', auth()->user()->id);
                    })->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])
                        ->whereNot('allowance_status', 1)
                        ->sum('amount'),

                'month' => $date->copy()->format('F, Y')
            );
        }

        return $data;
    }

    private function transactionData()
    {
        $start = Carbon::now()->subMonth(12);
        $end = Carbon::now();

        $data = [];

        for ($date = $start->copy(); $date->lte($end); $date->addMonth()) {
            $data[] = array(
                ''
            );
        }
    }
}
