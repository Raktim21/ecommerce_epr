<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentStoreRequest;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->paymentService->getAll()
        ]);
    }


    public function store(PaymentStoreRequest $request)
    {
        $status = $this->paymentService->store($request);

        if($status)
        {
            return response()->json([
                'success' => true,
            ], 201);
        }
        else {
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong.'
            ], 500);
        }
    }
}
