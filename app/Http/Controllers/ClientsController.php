<?php

namespace App\Http\Controllers;

use App\Exports\ClientExport;
use App\Http\Requests\ClientDeleteRequest;
use App\Http\Requests\ClientGetRequest;
use App\Http\Requests\ClientImportRequest;
use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateDocRequest;
use App\Http\Requests\ClientUpdateInfoRequest;
use App\Http\Requests\FileTypeRequest;
use App\Services\ClientService;
use Maatwebsite\Excel\Facades\Excel;

class ClientsController extends Controller
{
    private $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(ClientGetRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->clientService->getAll($request, auth()->user()->hasRole('Super Admin') ?? false),
            'search' => $request->search ?? ''
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
        if($this->clientService->create($request))
        {
            return response()->json([
                'success' => true,
            ], 201);
        }
        return response()->json([
            'success' => false,
        ], 500);
    }

    public function importClients(ClientImportRequest $request)
    {
        if($this->clientService->import($request))
        {
            return response()->json([
                'success' => true,
            ]);
        }
        else
        {
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong.'
            ], 500);
        }
    }

    public function ClientsExport(FileTypeRequest $request)
    {
        $file_name = 'client-list-' . date('dis') . '.' . $request->type;

        return Excel::download(new ClientExport(), $file_name);
    }

    public function updateInfo(ClientUpdateInfoRequest $request, $id)
    {
        if(!$this->clientService->isConfirmed($id))
        {
            $this->clientService->updateInfo($request, $id);

            return response()->json([
                'success' => true,
            ]);
        }
        return response()->json([
            'success' => false,
            'error' => 'The selected client can not be updated.'
        ], 422);
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
        $this->clientService->delete($request);

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
