<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\Payment;
use App\Models\Service;
use App\Models\PaymentType;
use App\Models\Website;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function getAll()
    {
        return Payment::leftJoin('clients','payments.client_id','=','clients.id')
            ->leftJoin('payment_types', 'payments.payment_type_id','=','payment_types.id')
            ->select('payments.*','clients.id as client_id','clients.name as client_name','payment_types.name as payment_type')
            ->paginate(10);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'client_id'         => $request->client_id,
                'payment_type_id'   => $request->payment_type_id,
                'service_id'        => $request->service_id,
                'transaction_id'    => $request->transaction_id ?? null,
                'invoice_no'        => 'PAY-'.rand(100,999).'-'.time(),
                'amount'            => Service::find($request->service_id)->price
            ]);

            Website::create([
                'client_id'     => $request->client_id,
                'domain'        => $request->website_domain
            ]);

            DB::commit();

            return $payment->id;
        }
        catch (\Exception $e)
        {
            DB::rollback();

            return 0;
        }
    }

    public function getAllTypes()
    {
        return PaymentType::latest()->get();
    }

    public function read($id)
    {
        return Payment::with('client','type','service')->findOrFail($id);
    }

    public function getData($client)
    {
        return Payment::with('client','type','service')->where('client_id',$client)->latest()->get();
    }

    public function getAllCategories()
    {
        return Service::when(request()->input('status') == 1, function ($q) {
            return $q->where('status', 1);
        })->get();
    }

    public function storeCategory(Request $request): void
    {
        Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);
    }


    public function updateCategory(Request $request , $id): void
    {
        Service::findOrFail($id)->update([
            'name'          => $request->name,
            'description'   => $request->description,
            'price'         => $request->price
        ]);
    }

    public function deleteCategory($id): void
    {
        $service = Service::findOrFail($id);

        $service->status = !$service->status;
        $service->save();
    }

}
