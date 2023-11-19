<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomBillCreateRequest;
use App\Services\CustomBillService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomBillController extends Controller
{
    private $service;

    public function __construct(CustomBillService $service)
    {
        $this->service = $service;
    }

    public function create(CustomBillCreateRequest $request)
    {
        if ($data = $this->service->store($request))
        {
            return response()->json([
                'success' => true,
                'data'    => array(
                                'custom_bill_id' => $data
                            )
            ], 201);
        }

        return response()->json(['success' => false, 'error' => 'Something went wrong'], 500);
    }

    public function get($id)
    {
        $data = Cache::remember('custom_bill'.$id, 24*60*60*7, function () use ($id) {
            return $this->service->getInfo($id);
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ], is_null($data) ? 204 : 200);
    }

    public function getPDF($id)
    {
        $data = Cache::get('custom_bill'.$id) ?? $this->service->getInfo($id);

        $info = array(
            'data' => $data
        );

        $pdf = Pdf::loadView('custom_bill', $info);

        return $pdf->stream('bill_' . now() . '.pdf');
    }
}
