<?php

namespace App\Http\Controllers;

use App\Http\Requests\PointRequest;
use App\Services\UserPointService;
use Illuminate\Http\Request;

class UserPointController extends Controller
{
    private $service;

    public function __construct(UserPointService $service)
    {
        $this->service = $service;
    }

    public function getList()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getTypes()
        ]);
    }

    public function updatePoint(PointRequest $request, $id)
    {
        $this->service->update($request, $id);

        return response()->json([
            'success' => true,
        ]);
    }
}
