<?php

namespace App\Http\Controllers;

use App\Http\Requests\KPILookUpRequest;
use App\Models\KPILookUp;
use App\Services\KPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KPILookUpController extends Controller
{
    private $service;

    public function __construct(KPIService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = Cache::remember('kpi_look_up', 24*60*60*7, function () {
            return $this->service->getAll();
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function create(KPILookUpRequest $request)
    {
        $this->service->store($request);

        return response()->json([
            'success' => true,
        ], 201);
    }

    public function update(KPILookUpRequest $request, $id)
    {
        $this->service->updateLookUp($request, $id);

        return response()->json([
            'success' => true,
        ]);
    }

    public function delete($id)
    {
        $this->service->deleteKpi($id);

        return response()->json([
            'success' => true
        ]);
    }
}
