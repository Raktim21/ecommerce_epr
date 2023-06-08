<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\FollowUpInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FollowUpInfoController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'detail' => 'required|string',
            'occurred_on' => 'required|date_format:Y-m-d H:i:s'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        FollowUpInfo::create([
            'client_id' => $request->client_id,
            'detail' => $request->detail,
            'occurred_on' => $request->occurred_on
        ]);

        return response()->json([
            'success' => true,
        ]);
    }


    public function show($client_id)
    {
        $client = Clients::findOrFail($client_id);

        $data = $client->follow_ups;

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    public function update(Request $request, $id)
    {
        $follow = FollowUpInfo::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'detail' => 'required|string',
            'occurred_on' => 'required|date_format:Y-m-d H:i:s'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $follow->update([
            'detail' => $request->detail,
            'occurred_on' => $request->occurred_on
        ]);

        return response()->json([
            'success' => true,
        ]);
    }


    public function delete($id)
    {
        $follow = FollowUpInfo::findOrFail($id);

        $follow->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
