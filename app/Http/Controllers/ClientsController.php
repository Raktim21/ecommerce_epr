<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientGetRequest;
use App\Models\Clients;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ClientsController extends Controller
{
    public function index(ClientGetRequest $request)
    {
        $search = $request->search ?? '';
        $status = $request->confirmed ?? '';

        $data = Clients::
            when($status==0, function ($query) use($search) {
                return $query->whereNull('confirmation_date')
                    ->when($search, function ($query) use ($search) {
                        return $query->where('company','like',"%$search%")
                            ->orWhere('clients.name','like',"%$search%")
                            ->orWhere('clients.email','like',"%$search%")
                            ->orWhere('clients.area','like',"%$search%");
                    });
            })
            ->when($status==1, function ($query) use($search) {
                return $query->whereNotNull('confirmation_date')
                    ->when($search, function ($query) use ($search) {
                        return $query->where('company','like',"%$search%")
                            ->orWhere('clients.name','like',"%$search%")
                            ->orWhere('clients.email','like',"%$search%")
                            ->orWhere('clients.area','like',"%$search%");
                    });
            })
            ->leftJoin('interest_statuses','clients.status_id','=','interest_statuses.id')
            ->select('clients.*','interest_statuses.id as status_id','interest_statuses.name as status_name')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    public function show($id)
    {
        $client = Clients::with('status_id')
            ->with(['added_by' => function($q) {
                $q->select('id','name');
            }])->findOrFail($id);

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
            'document'         => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
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
            'email'            => 'required|string|email|max:255|unique:clients,email,'.$id,
            'phone_no'         =>   [
                'required',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                'unique:clients,phone_no,'.$id,
            ],
            'status_id'        => 'required|exists:interest_statuses,id',
            'area'             => 'required|string',
            'client_opinion'   => 'nullable|string',
            'officer_opinion'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
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


    public function updateDoc(Request $request, $id)
    {
        $client = Clients::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'document' => 'required|mimes:pdf,jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        if($client->document)
        {
            if(File::exists(public_path($client->document)))
            {
                File::delete(public_path($client->document));
            }
        }

        $file = $request->file('document');
        $filename = hexdec(uniqid()). '.' . $file->getClientOriginalExtension();
        $file->move(public_path('/uploads/clients/documents/'),$filename);

        $client->update([
            'document' => '/uploads/clients/documents/' . $filename
        ]);

        return response()->json([
            'success' => true,
        ]);
    }


    public function destroy($id)
    {
        $client = Clients::findOrFail($id);

        try {
            $client->delete();

            return response()->json([
                'success' => true,
            ]);
        }
        catch (\Exception $e)
        {
            return response()->json([
                'success' => false,
                'error' => 'Cannot delete this client.'
            ], 500);
        }
    }


    public function changeStatus(Request $request, $id)
    {
        $client = Clients::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:interest_statuses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        $client->update([
            'status_id' => $request->status_id
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function unpaidClients()
    {
        $data = Clients::whereDoesntHave('payment')
            ->select('id','name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
