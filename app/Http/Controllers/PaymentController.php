<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index()
    {
        $data = Payment::leftJoin('clients','payments.client_id','=','clients.id')
            ->select('payments.*','clients.id as client_id','clients.name as client_name')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        Payment::create([
            'client_id' => $request->client_id,
            'amount' => $request->amount
        ]);

        return response()->json([
            'success' => true,
        ], 201);
    }
}
