<?php

namespace App\Http\Controllers;

use App\Http\Requests\PointRequest;
use App\Services\UserPointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserPointController extends Controller
{
    private $service;

    public function __construct(UserPointService $service)
    {
        $this->service = $service;
    }

    public function getList()
    {
        $data = Cache::remember('point_types', 24*60*60*7, function () {
            return $this->service->getTypes();
        });

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

//    public function pointData($user_id)
//    {
//        return response()->json([
//            'success' => true,
//            'data'    => $this->service->getUserPoints($user_id)
//        ]);
//    }

    public function updatePoint(PointRequest $request, $id)
    {
        $this->service->update($request, $id);

        return response()->json([
            'success' => true,
        ]);
    }
}
