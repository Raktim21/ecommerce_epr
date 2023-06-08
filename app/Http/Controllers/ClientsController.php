<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientsController extends Controller
{
    public function index()
    {
        $data = Clients::where('confirmation_date', null)->with('status_id')
            ->with(['added_by' => function($q) {
                $q->select('id','name');
            }])
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $client = Clients::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company'          => 'required|string|max:255',
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255|unique:clients,email',
            'phone_no'         =>   [
                                        'required',
                                        'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                                        'unique:clients,phone_no',
                                    ],
            'area'             => 'required|string',
            'client_opinion'   => 'nullable|string',
            'officer_opinion'  => 'nullable|string',
            'document'         => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $client = Clients::create([
            'company' => $request->company,
            'name' => $request->name,
            'email' => $request->email,
            'phone_no' => $request->phone_no,
            'area' => $request->area,
            'status_id' => 1,
            'client_opinion' => $request->client_opinion ?? 'N/A',
            'officer_opinion' => $request->officer_opinion ?? 'N/A',
            'added_by' => auth()->user()->id
        ]);

        if ($request->hasFile('document')) {

            $file = $request->file('document');
            $filename = hexdec(uniqid()). '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/uploads/clients/documents'),$filename);

            $client->document = '/uploads/clients/documents/' . $filename;
            $client->save();
        }

        return response()->json([
            'success' => true,
        ], 201);
    }

    public function updateInfo(Request $request, $id)
    {
        $client = Clients::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company'          => 'required|string|max:255',
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255|unique:clients,email',
            'phone_no'         =>   [
                'required',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                'unique:clients,phone_no',
            ],
            'status_id'        => 'required|exists:interest_statuses,id',
            'area'             => 'required|string',
            'client_opinion'   => 'nullable|string',
            'officer_opinion'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $client->update([
            'company' => $request->company,
            'name' => $request->name,
            'email' => $request->email,
            'phone_no' => $request->phone_no,
            'area' => $request->area,
            'status_id' => $request->status_id,
            'client_opinion' => $request->client_opinion ?? 'N/A',
            'officer_opinion' => $request->officer_opinion ?? 'N/A',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
