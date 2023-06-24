<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebsiteRequest;
use App\Services\WebsiteService;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    protected $service;

    public function __construct(WebsiteService $service)
    {
        $this->service = $service;
    }
    public function store(WebsiteRequest $request)
    {
        $this->service->store($request);

        return response()->json([
            'success' => true,
        ], 201);
    }
}
