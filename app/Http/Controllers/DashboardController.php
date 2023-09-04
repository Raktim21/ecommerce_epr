<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    private $dashboard;
    
    public function __construct(DashboardService $dashboard)
    {
        $this->dashboard = $dashboard;
    }


    public function index(){

        if (auth()->user()->hasRole('Super Admin')) {
            return response()->json([
                'success' => true,
                'data'    => $this->dashboard->adminDashboard(),
            ]);
        }else {
            return response()->json([
                'success' => true,
                'data'    => [],
            ]);
        }
    }
}
