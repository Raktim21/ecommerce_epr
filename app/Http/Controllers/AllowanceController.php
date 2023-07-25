<?php

namespace App\Http\Controllers;

use App\Exports\FoodAllowanceExport;
use App\Exports\TransportAllowanceExport;
use App\Http\Requests\AllowanceEndRequest;
use App\Http\Requests\AllowanceFilterRequest;
use App\Http\Requests\AllowanceStartRequest;
use App\Http\Requests\AllowanceStatusRequest;
use App\Http\Requests\AllowanceUpdateRequest;
use App\Http\Requests\FileTypeRequest;
use App\Http\Requests\FoodAllowanceStoreRequest;
use App\Http\Requests\TransportAllowancPaymentStatusRequest;
use App\Models\TransportAllowance;
use App\Models\User;
use App\Services\AllowanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AllowanceController extends Controller
{
    private $service;

    public function __construct(AllowanceService $service)
    {
        $this->service = $service;
    }

    public function transportAllowance($id)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getTransportAllowance($id),
        ]);
    }

    public function foodAllowance($id)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getFoodAllowance($id)
        ]);
    }

    public function transportAllowanceSearch(AllowanceFilterRequest $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getTransportSearchResult($request),
            'search'  => array(
                'name'                  => $request->search,
                'start_date'            => $request->start_date,
                'end_date'              => $request->end_date,
                'amount_start_range'    => $request->amount_start_range,
                'amount_end_range'      => $request->amount_end_range,
                'status'                => $request->status,
            )
        ]);
    }

    public function foodAllowanceSearch(AllowanceFilterRequest $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->getFoodSearchResult($request),
            'search'  => array(
                'name'                  => $request->search,
                'start_date'            => $request->start_date,
                'end_date'              => $request->end_date,
                'amount_start_range'    => $request->amount_start_range,
                'amount_end_range'      => $request->amount_end_range,
                'status'                => $request->status,
            )
        ]);
    }

    public function currentTransportAllowance()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->currentTransport(),
        ]);
    }

    public function start(AllowanceStartRequest $request)
    {
        if($this->service->startJourney($request))
        {
            return response()->json(['success' => true], 201);
        }
        return response()->json([
            'success' => false,
            'error'   => 'You cannot start a new journey without ending previous one.'
        ], 422);
    }

    public function end(AllowanceEndRequest $request, $id)
    {
        $status = $this->service->endJourney($request, $id);
        if($status == 1)
        {
            return response()->json([
                'success' => false,
                'error' => 'You have already entered the information.'
            ],422);
        }
        else if($status == 2)
        {
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to update the information.'
            ],401);
        }

        return response()->json(['success' => true]);
    }

    public function update(AllowanceUpdateRequest $request, $id)
    {
        if($this->service->updateInfo($request, $id))
        {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'error' => 'You cannot update this allowance.'],422);
    }

    public function changeStatus(AllowanceStatusRequest $request, $id)
    {
        $this->service->updateStatus($request, $id);
        return response()->json(['success' => true]);
    }

    public function transportAllowanceChangePaymentStatus(TransportAllowancPaymentStatusRequest $request){

        foreach ($request->transport_allowance_id as $value) {
            $this->service->transportAllowanceUpdatePaymentStatus($request, $value);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status of a transport allowance has been changed.'
        ]);
        
    }


    public function transportAllowancePaymentSlip(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'transport_allowance_id'   => 'required|array',
            'transport_allowance_id.*' => 'required|exists:transport_allowances,id',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validate->errors()->first()
            ], 422);
        }


        if (TransportAllowance::whereRaw('id IN (' . implode(',', $request->transport_allowance_id) . ')')->distinct('created_by')->count() > 1) {
           
            return response()->json([
                'success' => false,
                'errors'  => "Please select only one user's allowance."
            ],304);
        }


        $user =  User::find(TransportAllowance::find($request->transport_allowance_id[0])->created_by);

        $data = [
            'transport_allowances' => TransportAllowance::whereRaw('id IN (' . implode(',', $request->transport_allowance_id) . ')')->get(),
            'user' => $user,
        ];

        
        $pdf = PDF::loadView('transport_allowance_payment_slip', compact('data'));
        return $pdf->stream('travel_alowance_payment_slip_'. $user->id . '-'. rand(1000, 9999) .'.pdf');

    }

    public function foodAllowanceStore(FoodAllowanceStoreRequest $request)
    {
        $this->service->createFoodAllowance($request);
        return response()->json(['success' => true],201);
    }

    public function foodAllowanceDelete($id)
    {
        if($this->service->deleteFoodAllowance($id))
        {
            return response()->json(['success' => true]);
        }
        return response()->json([
            'success' => false,
            'error'   => 'You cannot delete this food allowance now.'
        ],422);
    }

    public function foodAllowanceUpdate(AllowanceStatusRequest $request, $id)
    {
        $this->service->updateFoodStatus($request, $id);
        return response()->json(['success' => true]);
    }

    public function transportAllowanceExport(FileTypeRequest $request)
    {
        $file_name = 'transport_allowance' . date('dis') . '.' . $request->type;

        return Excel::download(new TransportAllowanceExport(), $file_name);
    }

    public function foodAllowanceExport(FileTypeRequest $request)
    {
        $file_name = 'food_allowance' . date('dis') . '.' . $request->type;

        return Excel::download(new FoodAllowanceExport(), $file_name);
    }

}
