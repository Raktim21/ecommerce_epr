<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowUpStoreRequest;
use App\Http\Requests\FollowUpUpdateRequest;
use App\Models\FollowUpInfo;
use App\Services\FollowUpService;

class FollowUpInfoController extends Controller
{
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
        $follow = FollowUpInfo::findOrFail($id);

        $follow->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
