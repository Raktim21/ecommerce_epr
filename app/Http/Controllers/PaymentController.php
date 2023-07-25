<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentCategoryStoreRequest;
use App\Http\Requests\PaymentDataRequest;
use App\Http\Requests\PaymentStoreRequest;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    protected $paymentService;
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

    public function getTypes()
    {
        return response()->json([
            'success' => true,
            'data' => $this->paymentService->getAllTypes()
        ]);
    }

    public function getCategories()
    {
        return response()->json([
            'success' => true,
            'data' => $this->paymentService->getAllCategories()
        ]);
    }

    public function storeCategories(PaymentCategoryStoreRequest $request)
    {
        $this->paymentService->storeCategory($request);

        return response()->json([
            'success' => true
        ],201);
    }

    public function store(PaymentStoreRequest $request)
    {
        $status = $this->paymentService->store($request);

        if($status != 0)
        {
            return response()->json([
                'success' => true,
                'payment_id' => $status
            ], 201);
        }
        else {
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong.'
            ], 500);
        }
    }

    public function getPayData(PaymentDataRequest $request)
    {
        return response()->json([
            'success' => 'true',
            'data' => $this->paymentService->getData($request->input('client_id'))
        ]);
    }

    public function getPayslip($id)
    {
        $data = $this->paymentService->read($id);

        $info = array(
            'data' => $data
        );

        $pdf = Pdf::loadView('payslip', $info);

        return $pdf->stream('payslip_' . now() . '.pdf');
    }

    public function deleteCategories($id)
    {
        if($this->paymentService->deleteCategory($id))
        {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 500);
    }
}
