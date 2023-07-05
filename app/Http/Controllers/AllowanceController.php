<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllowanceEndRequest;
use App\Http\Requests\AllowanceStartRequest;
use App\Http\Requests\AllowanceStatusRequest;
use App\Http\Requests\AllowanceUpdateRequest;
use App\Services\AllowanceService;

class AllowanceController extends Controller
{
    private $service;

    public function __construct(AllowanceService $service)
    {
        $this->service = $service;
    }

    public function transportAllowanceList(){

        return response()->json([
            'success' => true,
            'data'    => $this->service->getAllTransportAllowance(),
        ]);
    }

    public function foodAllowanceList()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getAllFoodAllowance(),
        ]);
    }

    public function start(AllowanceStartRequest $request)
    {
        if($this->service->startJourney($request))
        {
            return response()->json(['success' => true], 201);
        }
        return response()->json([
            'success' => false,
            'error'   => 'You cannot start a new journey without ending previous one.'
        ], 422);
    }

    public function end(AllowanceEndRequest $request, $id)
    {
        $status = $this->service->endJourney($request, $id);
        if($status == 1)
        {
            return response()->json([
                'success' => false,
                'error' => 'You have already entered the information.'
            ],422);
        }
        else if($status == 2)
        {
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to update the information.'
            ],401);
        }
        else if($status == 3)
        {
            return response()->json([
                'success' => false,
                'error' => 'Please provide all required information first.'
            ],422);
        }

        return response()->json(['success' => true]);
    }

    public function update(AllowanceUpdateRequest $request, $id)
    {
        $this->service->updateInfo($request, $id);
        return response()->json(['success' => true]);
    }

    public function changeStatus(AllowanceStatusRequest $request, $id)
    {
        $this->service->updateStatus($request, $id);
        return response()->json(['success' => true]);
    }

}
