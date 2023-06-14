<?php

namespace App\Http\Controllers;

use App\Services\StatusService;

class InterestStatusController extends Controller
{
    public function __construct(StatusService $statusService)
    {
        $this->statusService = $statusService;
    }


    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->statusService->getAll()
        ]);
    }
}
