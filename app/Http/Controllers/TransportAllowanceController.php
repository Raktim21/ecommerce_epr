<?php

namespace App\Http\Controllers;

use App\Models\TransportAllowance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TransportAllowanceController extends Controller
{

    public function index(){
        
        return response()->json([
            'success' => true,
            'data'    => TransportAllowance::where('created_by', auth()->user()->id)->get()
        ],200);
    }


    public function start(Request $request){
        $request->validate([
            'from_lat'       => 'required',
            'from_lng'       => 'required',
            'visit_type'     => 'required|string',
            'transport_type' => 'nullable|string',
            'amount'         => 'nullable|numeric',
            'document'       => 'nullable|file',
            'note'           => 'nullable|string',
        ]);

        DB::beginTransaction();

        try{
            $allowance = new TransportAllowance();
            $allowance->from_lat       = $request->from_lat;
            $allowance->from_lng       = $request->from_lng;
            $allowance->from_address   = getAddress($request->from_lat, $request->from_lng);
            $allowance->start_time     = Carbon::now()->timezone('Asia/Dhaka');;
            $allowance->visit_type     = $request->visit_type;
            $allowance->transport_type = $request->transport_type ?? null;
            $allowance->amount         = $request->amount ?? 0.00;
            $allowance->note           = $request->note ?? null;
            $allowance->save();

            
            if ($request->hasFile('document')){
                saveImage($request->file('document'), 'uploads/travel_allowance/documents/', $allowance, 'document');
            }

            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'You started a new trsport allowance.'
            ],200);


        }catch(\Exception $ex){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ],500);
        }

        
    }



    public function end(Request $request){
        $request->validate([
            'to_lat'         => 'required',
            'to_lng'         => 'required',
            'visit_type'     => 'required|string',
            'transport_type' => 'nullable|string',
            'amount'         => 'nullable|numeric',
            'document'       => 'nullable|file',
            'note'           => 'nullable|string',
        ]);

        DB::beginTransaction();

        try{
            $allowance = new TransportAllowance();
            $allowance->from_lat       = $request->from_lat;
            $allowance->from_lng       = $request->from_lng;
            $allowance->from_address   = getAddress($request->from_lat, $request->from_lng);
            $allowance->start_time     = Carbon::now()->timezone('Asia/Dhaka');;
            $allowance->visit_type     = $request->visit_type;
            $allowance->transport_type = $request->transport_type ?? null;
            $allowance->amount         = $request->amount ?? 0.00;
            $allowance->note           = $request->note ?? null;
            $allowance->save();

            
            if ($request->hasFile('document')){
                saveImage($request->file('document'), 'uploads/travel_allowance/documents/', $allowance, 'document');
            }

            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'You started a new trsport allowance.'
            ],200);


        }catch(\Exception $ex){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ],500);
        }

        
    }













    private function getAddress($lat, $lng)
    {
         
    }
}
