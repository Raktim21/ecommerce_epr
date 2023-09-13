<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceStoreRequest;
use App\Http\Requests\PaymentDataRequest;
use App\Http\Requests\PaymentStoreRequest;
use App\Models\Payment;
use App\Models\Service;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    protected $paymentService;
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $data = $this->paymentService->getAll();

        return response()->json([
            'success' => true,
            'data' => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function getTypes()
    {
        $data = Cache::rememberForever('payment_type', function () {
            return $this->paymentService->getAllTypes();
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getCategories()
    {
        $data = Cache::remember('services', 24*60*60*7, function () {
            return $this->paymentService->getAllCategories();
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function storeCategories(ServiceStoreRequest $request)
    {
        $this->paymentService->storeCategory($request);

        return response()->json([
            'success' => true
        ],201);
    }

    public function updateCategories(ServiceStoreRequest $request, $id)
    {
        $this->paymentService->updateCategory($request, $id);

        return response()->json([
            'success' => true,
        ]);
    }

    public function statusCategories($id)
    {
        $this->paymentService->deleteCategory($id);
        return response()->json(['success' => true]);
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


}
