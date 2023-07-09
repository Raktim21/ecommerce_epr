<?php

namespace App\Http\Controllers;

use App\Exports\FoodAllowanceExport;
use App\Exports\TransportAllowanceExport;
use App\Http\Requests\AllowanceEndRequest;
use App\Http\Requests\AllowanceFilterRequest;
use App\Http\Requests\AllowanceStartRequest;
use App\Http\Requests\AllowanceStatusRequest;
use App\Http\Requests\AllowanceUpdateRequest;
use App\Http\Requests\FileTypeRequest;
use App\Http\Requests\FoodAllowanceStoreRequest;
use App\Services\AllowanceService;
use Maatwebsite\Excel\Facades\Excel;

class AllowanceController extends Controller
{
    private $service;

    public function __construct(AllowanceService $service)
    {
        $this->service = $service;
    }

    public function transportAllowance($id)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getTransportAllowance($id),
        ]);
    }

    public function foodAllowance($id)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getFoodAllowance($id),
        ]);
    }

    public function transportAllowanceSearch(AllowanceFilterRequest $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getTransportSearchResult($request),
        ]);
    }

    public function foodAllowanceSearch(AllowanceFilterRequest $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getFoodSearchResult($request),
        ]);
    }

    public function currentTransportAllowance()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->currentTransport(),
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

        return response()->json(['success' => true]);
    }

    public function update(AllowanceUpdateRequest $request, $id)
    {
        if($this->service->updateInfo($request, $id))
        {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'error' => 'You cannot update this allowance.'],422);
    }

    public function changeStatus(AllowanceStatusRequest $request, $id)
    {
        $this->service->updateStatus($request, $id);
        return response()->json(['success' => true]);
    }

    public function foodAllowanceStore(FoodAllowanceStoreRequest $request)
    {
        $this->service->createFoodAllowance($request);
        return response()->json(['success' => true],201);
    }

    public function foodAllowanceDelete($id)
    {
        if($this->service->deleteFoodAllowance($id))
        {
            return response()->json(['success' => true]);
        }
        return response()->json([
            'success' => false,
            'error'   => 'You cannot delete this food allowance now.'
        ],422);
    }

    public function foodAllowanceUpdate(AllowanceStatusRequest $request, $id)
    {
        $this->service->updateFoodStatus($request, $id);
        return response()->json(['success' => true]);
    }

    public function transportAllowanceExport(FileTypeRequest $request)
    {
        $file_name = 'transport_allowance' . date('dis') . '.' . $request->type;

        return Excel::download(new TransportAllowanceExport(), $file_name);
    }

    public function foodAllowanceExport(FileTypeRequest $request)
    {
        $file_name = 'food_allowance' . date('dis') . '.' . $request->type;

        return Excel::download(new FoodAllowanceExport(), $file_name);
    }

}
