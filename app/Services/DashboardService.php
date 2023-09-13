<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\EmployeeProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    private $clients ;

    public function __construct(Clients $clients)
    {
        $this->clients = $clients;

    }

    public function adminDashboard()
    {
        $clients            = $this->clients->whereNull('confirmation_date');
        $confirmed_clients  = $this->clients->whereNotNull('confirmation_date');

        $monthly_client_data   = [];

        $startDate = Carbon::now()->subMonth(12);
        $endDate   = Carbon::now();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMonth()) {
            $monthly_client_data[$date->copy()->format('F, Y')]   = array(
                'clients' => $clients->clone()->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])->count(),
                'confirmed_clients' => $confirmed_clients->clone()->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])->count()
            );
        }

        return [
            'total_client'                  => $clients->clone()->count(),
            'total_confirmed_client'        => $confirmed_clients->clone()->count(),
            'monthly_client_data'           => $monthly_client_data,
            'user_point_report'             => $this->userPointReport(),
            'employee_kpi'                  => $this->employeeKPI(),
            'star_employee'                 => $this->starEmployee(),
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
