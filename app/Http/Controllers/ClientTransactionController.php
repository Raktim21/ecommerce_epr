<?php

namespace App\Http\Controllers;

use App\Exports\ClientTransactionsExport;
use App\Http\Requests\ClientImportRequest;
use App\Http\Requests\ClientTransactionRequest;
use App\Http\Requests\FileTypeRequest;
use App\Services\ClientTransactionService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ClientTransactionController extends Controller
{
    protected $service;

    public function __construct(ClientTransactionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {}

    public function exportData(FileTypeRequest $request)
    {
        if ($request->type != 'pdf')
        {
            $file_name = 'client-transactions-' . date('dis') . '.' . $request->type;
            return Excel::download(new ClientTransactionsExport(), $file_name);
        }
        else {}
    }

    public function store(ClientTransactionRequest $request)
    {
        $this->service->storeTransaction($request);

        return response()->json([
            'success' => true
        ], 201);
    }

    public function importData(ClientImportRequest $request)
    {
        $this->service->importTransactions($request);

        return response()->json(['success' => true], 201);
    }
}
