<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillStoreRequest;
use App\Services\BillService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public $service;

    public function __construct(BillService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getBills($request);

        return response()->json([
            'success' => true,
            'data'    => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function store(BillStoreRequest $request)
    {
        $bill = $this->service->storeBill($request);

        if($bill != 0)
        {
            return response()->json([
                'success' => true,
                'data'    => array(
                                'bill_id' => $bill
                            )
            ], 201);
        }
        return response()->json([
            'success' => false,
            'error' => 'Something went wrong.'
        ], 500);
    }

    public function billSlip($id)
    {
        $data = $this->service->getBill($id);

        $info = array(
            'data' => $data
        );

        $pdf = Pdf::loadView('bill', $info);

        return $pdf->stream('bill_' . now() . '.pdf');
    }
}
