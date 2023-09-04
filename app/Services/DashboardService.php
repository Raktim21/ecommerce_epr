<?php

namespace App\Services;

use App\Models\Clients;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    private $clients ;

    public function __construct(Clients $clients)
    {
        $this->clients = $clients;

    }

    public function adminDashboard(){

        $start_date = Carbon::parse(request()->start_date) ?? Carbon::now()->format('m');

        $leads   = $this->clients->whereNull('confirmation_date');
        $clients = $this->clients->whereNotNull('confirmation_date');

        $leads_month_count   = [];
        $clients_month_count = [];

        $startDate = Carbon::now()->subMonth(12);
        $endDate   = Carbon::now();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMonth()) {
            $leads_month_count[$date->copy()->format('F')]   = $leads->clone()->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])->count();
            $clients_month_count[$date->copy()->format('F')] = $clients->clone()->whereBetween('created_at', [Carbon::parse($date->copy()->startOfMonth()), Carbon::parse($date->copy()->endOfMonth())])->count();
        }


        $data = [
            'total_lead'          => $leads->clone()->count(),
            'total_client'        => $clients->clone()->count(),
            'leads_month_count'   => $leads_month_count,
            'clients_month_count' => $clients_month_count
        ];

        return  $data;
    }
}