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
        $this->service->endJourney($request, $id);
        return response()->json(['success' => true]);
    }

}
