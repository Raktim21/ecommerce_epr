<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllowanceEndRequest;
use App\Http\Requests\AllowanceStartRequest;
use App\Models\TransportAllowance;
use App\Services\AllowanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransportAllowanceController extends Controller
{
    private $service;

    public function __construct(AllowanceService $service)
    {
        $this->service = $service;
    }

    public function index(){

        return response()->json([
            'success' => true,
            'data'    => $this->service->getAll(),
        ]);
    }

    public function start(AllowanceStartRequest $request)
    {
        $this->service->startJourney($request);
        return response()->json(['success' => true], 201);
    }

    public function end(AllowanceEndRequest $request, $id)
    {
        $status = $this->service->endJourney($request, $id);
        if($status == 1)
        {
            return response()->json(['success' => false, 'error' => 'You have already entered the information.'],422);
        }
        else if($status == 2)
        {
            return response()->json(['success' => false, 'error' => 'You are not authorized to update the information.'],401);
        }
        return response()->json(['success' => true]);
    }

}
