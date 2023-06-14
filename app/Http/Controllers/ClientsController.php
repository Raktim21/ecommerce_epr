<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientGetRequest;
use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateInfoRequest;
use App\Imports\ClientsImport;
use App\Models\Clients;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ClientsController extends Controller
{
    public function index(ClientGetRequest $request)
    {
        $search = $request->search ?? '';
        $status = $request->confirmed ?? '';

        $data = Clients::
            when($status==0, function ($query) use($search) {
            return $query->where(function ($query) use ($search) {
                        $query->where('company','like',"%$search%")
                            ->orWhere('clients.name','like',"%$search%")
                            ->orWhere('clients.email','like',"%$search%")
                            ->orWhere('clients.area','like',"%$search%");
                })->whereNull('confirmation_date');
            })->
            when($status==1, function ($query) use($search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('company','like',"%$search%")
                        ->orWhere('clients.name','like',"%$search%")
                        ->orWhere('clients.email','like',"%$search%")
                        ->orWhere('clients.area','like',"%$search%");
                })->whereNotNull('confirmation_date');
            })
            ->leftJoin('interest_statuses','clients.status_id','=','interest_statuses.id')
            ->select('clients.*','interest_statuses.id as status_id','interest_statuses.name as status_name')
            ->paginate(10)->appends($request->except('page'));

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    public function show($id, ClientService $clientService)
    {
        $client = $clientService->show($id);

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }


    public function store(ClientStoreRequest $request, ClientService $clientService)
    {
        $clientService->create($request);

        return response()->json([
            'success' => true,
        ], 201);
    }


    public function importClients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,csv',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        $file = $request->file('file');

        try {
            Excel::import(new ClientsImport, $file);

            return response()->json([
                'success' => true,
            ], 201);
        }
        catch (\Exception $ex)
        {
            return response()->json([
                'success' => false,
                'error' => $ex->getMessage()
            ], 422);
        }
    }


    public function updateInfo(ClientUpdateInfoRequest $request, $id, ClientService $clientService)
    {
        $clientService->update($request, $id);

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

    public function demo()
    {
        Clients::truncate();
    }
}
