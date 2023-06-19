<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowUpStoreRequest;
use App\Http\Requests\FollowUpUpdateRequest;
use App\Models\Clients;
use App\Services\FollowUpService;
use Illuminate\Support\Facades\DB;

class FollowUpInfoController extends Controller
{
    private $followUpService;

    public function __construct(FollowUpService $followUpService)
    {
        $this->followUpService = $followUpService;
    }

    public function store(FollowUpStoreRequest $request)
    {
        $this->followUpService->store($request);

        return response()->json([
            'success' => true,
        ],201);
    }


    // protected function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    //     $radius = 6371; // Earth's radius in kilometers

    //     $lat1Rad = deg2rad($lat1);
    //     $lon1Rad = deg2rad($lon1);
    //     $lat2Rad = deg2rad($lat2);
    //     $lon2Rad = deg2rad($lon2);

    //     $deltaLat = $lat2Rad - $lat1Rad;
    //     $deltaLon = $lon2Rad - $lon1Rad;

    //     $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
    //          cos($lat1Rad) * cos($lat2Rad) *
    //          sin($deltaLon / 2) * sin($deltaLon / 2);
    //     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    //     $distance = $radius * $c;

    //     return round($distance, 2);
    // }

    public function show($client_id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->followUpService->show($client_id)
        ]);
    }

    public function update(FollowUpUpdateRequest $request, $id)
    {
        $this->followUpService->update($request, $id);

        return response()->json([
            'success' => true,
        ]);
    }

    public function delete($id)
    {
        $this->followUpService->delete($id);

        return response()->json([
            'success' => true,
        ]);
    }
}
