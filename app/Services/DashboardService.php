<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\EmployeeProfile;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    private $employee, $clients, $service;

    public function __construct(EmployeeProfile $employee, Clients $clients, Service $service)
    {
        $this->employee = $employee;
        $this->clients  = $clients;
        $this->service  = $service;
    }

    public function adminDashboard()
    {
        $employees             = $this->employee;
        $clients               = $this->clients->whereNull('confirmation_date');
        $confirmed_clients     = $this->clients->whereNotNull('confirmation_date');
        $services              = $this->service->where('status', 1);

        $monthly_client_data   = [];

        $startDate = Carbon::now()->subMonth(12);
        $endDate   = Carbon::now();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMonth()) {
            $monthly_client_data[$date->copy()->format('F, Y')]   = array(
                'clients'           => $clients->clone()->when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                                            return $q->where('added_by', auth()->user()->id);
                                        })->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])
                                        ->count(),

                'confirmed_clients' => $confirmed_clients->clone()->when(!auth()->user()->hasRole('Super Admin'), function ($q) {
                                            return $q->where('added_by', auth()->user()->id);
                                        })->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])
                                        ->count()
            );
        }

        return [
            'total_employees'               => $employees->clone()->whereHas('user', function ($q) {
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

            'services'                      => $services->count(),
            'star_employee'                 => $this->starEmployee(),
            'monthly_client_data'           => $monthly_client_data,
            'user_point_report'             => auth()->user()->hasRole('Super Admin') ? $this->userPointReport() : null,
            'employee_kpi'                  => auth()->user()->hasRole('Super Admin') ? $this->employeeKPI() : null,
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
}
