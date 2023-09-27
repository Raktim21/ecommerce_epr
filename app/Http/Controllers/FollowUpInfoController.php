<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowUpReminderRequest;
use App\Http\Requests\FollowUpStoreRequest;
use App\Http\Requests\FollowUpUpdateRequest;
use App\Services\FollowUpService;
use Illuminate\Support\Facades\Cache;

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

    public function addReminder(FollowUpReminderRequest $request)
    {
        $this->followUpService->storeReminder($request);

        return response()->json([
            'success' => true
        ], 201);
    }

    public function show($client_id)
    {
        $data = Cache::remember('client_follow_up'.$client_id, 24*60*60*7, function () use ($client_id) {
            return $this->followUpService->show($client_id);
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ], count($data) == 0 ? 204 : 200);
    }

    public function getFollowUps()
    {
        $data = $this->followUpService->getPendingFollowUps();

        return response()->json([
            'success'   => true,
            'data'      => $data
        ], count($data) == 0 ? 204 : 200);
    }

    public function update(FollowUpUpdateRequest $request, $id)
    {
        if ($this->followUpService->update($request, $id)) {
            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error'   => 'Cannot add follow up for a confirmed client.'
            ], 400);
        }
    }

    public function delete($id)
    {
        $this->followUpService->delete($id);

        return response()->json([
            'success' => true,
        ]);
    }
}
