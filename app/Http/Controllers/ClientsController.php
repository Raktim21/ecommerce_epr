<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientDeleteRequest;
use App\Http\Requests\ClientGetRequest;
use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateDocRequest;
use App\Http\Requests\ClientUpdateInfoRequest;
use App\Http\Requests\ClientUpdateStatusRequest;
use App\Imports\ClientsImport;
use App\Models\Clients;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ClientsController extends Controller
{
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

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


    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->clientService->show($id)
        ]);
    }


    public function store(ClientStoreRequest $request)
    {
        $this->clientService->create($request);

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


    public function updateInfo(ClientUpdateInfoRequest $request, $id)
    {
        $this->clientService->updateInfo($request, $id);

        return response()->json([
            'success' => true,
        ]);
    }


    public function updateDoc(ClientUpdateDocRequest $request, $id)
    {
        $this->clientService->updateDoc($request, $id);

        return response()->json([
            'success' => true,
        ]);
    }


    public function destroy(ClientDeleteRequest $request)
    {
    }


    public function changeStatus(ClientUpdateStatusRequest $request, $id)
    {
        $this->clientService->updateStatus($request, $id);

        return response()->json([
            'success' => true,
        ]);
    }

    public function unpaidClients()
    {
        return response()->json([
            'success' => true,
            'data' => $this->clientService->unpaidClients()
        ]);
    }
}
