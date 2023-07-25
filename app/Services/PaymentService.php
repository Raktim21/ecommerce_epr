<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\PaymentType;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'client_id' => $request->client_id,
                'payment_type_id' => $request->payment_type_id,
                'payment_category_id' => $request->payment_category_id,
                'transaction_id' => $request->transaction_id ?? null,
                'invoice_no' => 'PAY-'.rand(100,999).'-'.time(),
                'amount' => $request->amount
            ]);

            Clients::find($request->client_id)->update([
                'confirmation_date' => Carbon::now(),
            ]);

            (new UserPointService())->savePoints(2);

            DB::commit();

            (new UserService)->sendNotification('New payment has been stored.', 'client-payment', $payment->client_id);

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
        return PaymentType::all();
    }

    public function read($id)
    {
        return Payment::with('client','type','category')->findOrFail($id);
    }

    public function getData($client)
    {
        return Payment::with('client','type','category')->where('client_id',$client)->latest()->get();
    }

    public function getAllCategories()
    {
        return PaymentCategory::get();
    }

    public function storeCategory(Request $request): void
    {
        PaymentCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);
    }

    public function deleteCategory($id): bool
    {
        try {
            PaymentCategory::findOrFail($id)->delete();
            return true;
        } catch (QueryException $ex)
        {
            return false;
        }
    }

}
