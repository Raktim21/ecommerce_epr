<?php

namespace App\Http\Controllers;

use App\Exports\ClientTransactionsExport;
use App\Http\Requests\ClientImportRequest;
use App\Http\Requests\ClientTransactionRequest;
use App\Http\Requests\ClientTransactionSearchRequest;
use App\Http\Requests\FileTypeRequest;
use App\Models\ClientTransaction;
use App\Services\ClientTransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ClientTransactionController extends Controller
{
    protected $service;

    public function __construct(ClientTransactionService $service)
    {
        $this->service = $service;
    }

    public function index(ClientTransactionSearchRequest $request)
    {
        $data = $this->service->getAll($request);

        return response()->json([
            'success' => true,
            'data'    => $data
        ], $data->isEmpty() ? 204 : 200);
    }

    public function exportData(FileTypeRequest $request)
    {
        if ($request->type != 'pdf') {
            $file_name = 'client-transactions-' . date('dis') . '.' . $request->type;
            return Excel::download(new ClientTransactionsExport(), $file_name);
        }

        if(!$request->client_id)
        {
            return response()->json([
                'success' => false,
                'error'   => 'Export to pdf is preferred only when client is selected.'
            ], 400);
        }

        $data = $this->service->getClientTransactions($request->client_id);

        if (count($data) != 0){
            $info = array(
                'data' => $data
            );

            $pdf = Pdf::loadView('client_transactions', $info);

            return $pdf->stream('client_transactions_' . now() . '.pdf');
        }

        return response()->json([
            'success' => false,
            'error'   => 'No data found.'
        ], 400);
    }

    public function exportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        $data = ClientTransaction::whereIn('client_transactions.id', $request->ids)
            ->leftJoin('payment_types','client_transactions.payment_type_id','=','payment_types.id')
            ->leftJoin('clients','client_transactions.client_id','=','clients.id')
            ->select('client_transactions.*','clients.name','clients.email','clients.phone_no','clients.company','clients.confirmation_date',
                'payment_types.name as payment_type')
            ->latest('client_transactions.created_at')->get();

        if (count($data) != 0) {
            $data = json_decode($data, true);

            $client_id = $data[0]['client_id'];

            $diffClient = array_filter($data, function ($item) use ($client_id) {
                return $item['client_id'] !== $client_id;
            });

            if(empty($diffClient))
            {
                $info = array(
                    'data' => $data
                );

                $pdf = Pdf::loadView('client_transactions', $info);

                return $pdf->stream('client_transactions_' . now() . '.pdf');
            } else {
                return response()->json([
                    'success' => false,
                    'error'   => 'Selected transactions must be of same client.'
                ], 422);
            }
        }
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
