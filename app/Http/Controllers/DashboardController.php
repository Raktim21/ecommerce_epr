<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private $dashboard;

    public function __construct(DashboardService $dashboard)
    {
        $this->dashboard = $dashboard;
    }


    public function index()
    {
        $data = Cache::remember('dashboard', 24*60*60, function () {
            return $this->dashboard->adminDashboard();
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
