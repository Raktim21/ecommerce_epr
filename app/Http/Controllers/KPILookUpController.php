<?php

namespace App\Http\Controllers;

use App\Http\Requests\KPILookUpRequest;
use App\Models\KPILookUp;
use App\Services\KPIService;
use Illuminate\Http\Request;

class KPILookUpController extends Controller
{
    private $service;

    public function __construct(KPIService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getAll(),
        ]);
    }

    public function create(KPILookUpRequest $request)
    {
        $this->service->store($request);

        return response()->json([
            'success' => true,
        ], 201);
    }

    public function update()
    {}
}
