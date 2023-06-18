<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientDeleteRequest;
use App\Http\Requests\ClientGetRequest;
use App\Http\Requests\ClientImportRequest;
use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateDocRequest;
use App\Http\Requests\ClientUpdateInfoRequest;
use App\Services\ClientService;

class ClientsController extends Controller
{
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(ClientGetRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->clientService->getAll($request),
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
        $this->clientService->create($request);

        return response()->json([
            'success' => true,
        ], 201);
    }

    public function importClients(ClientImportRequest $request)
    {
        $status = $this->clientService->import($request);

        if($status)
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
