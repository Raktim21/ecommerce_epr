<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'client_id' => ['required','unique:payments,client_id',
                function ($attr, $val, $fail)
                {
                    $client = Clients::find($val);

                    if(!$client || $client->status_id != 11) {
                        $fail("The selected client must have an interest rate of 100.");
                    }
                }],
            'amount' => 'required|numeric',
        ],[
            'client_id.unique' => 'The selected client has already paid.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();

        try {
            Payment::create([
                'client_id' => $request->client_id,
                'amount' => $request->amount
            ]);

            Clients::find($request->client_id)->update([
                'confirmation_date' => Carbon::now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ], 201);
        }
        catch (\Exception $e)
        {
            DB::rollback();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
