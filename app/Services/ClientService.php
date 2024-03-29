<?php

namespace App\Services;

use App\Imports\ClientsImport;
use App\Models\Clients;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ClientService
{
    private $client;

    public function __construct(Clients $client)
    {
        $this->client = $client;
    }

    public function getFilteredClient(Request $request, $isSuperAdmin)
    {
        $search = $request->search ?? '';
        $status = $request->confirmed ?? 0;
        $limit = $request->per_page;

        return $this->client->clone()
        ->when($status==0, function ($query) use($search) {
            return $query->where(function ($query) use ($search) {
                $query->where('company','like',"%$search%")
                    ->orWhere('clients.name','like',"%$search%")
                    ->orWhere('clients.email','like',"%$search%");
            })->whereNull('confirmation_date');
        })
        ->when($status==1, function ($query) use($search) {
            return $query->where(function ($query) use ($search) {
                $query->where('company','like',"%$search%")
                    ->orWhere('clients.name','like',"%$search%")
                    ->orWhere('clients.email','like',"%$search%")
                    ->orWhereHas('payment', function ($q) use ($search) {
                        return $q->where('invoice_no','like',"%$search%");
                    });
            })->whereNotNull('confirmation_date')->with('website');
        })
            ->leftJoin('payments','clients.id','=','payments.client_id')
            ->leftJoin('users','clients.added_by','=','users.id')
            ->select('clients.*','payments.id as payment_id','payments.invoice_no as payment_invoice','users.name as added_by')
            ->withCount('transactions')
            ->withCount('follow_ups')
            ->when($isSuperAdmin==false, function($query) {
                return $query->where('clients.added_by', auth()->user()->id);
            })
            ->orderByDesc('clients.id')
            ->paginate($limit)
            ->appends($request->except('page','per_page'));
    }

    public function show($id)
    {
        $data = $this->client->clone()
            ->when(auth()->user()->hasRole('Super Admin'), function ($q) {
                return $q->with(['added_by' => function($q1) {
                    $q1->select('id','name');
                }]);
            })
            ->with('payment.type', 'payment.service', 'website')
            ->find($id);

        if(auth()->user()->hasRole('Super Admin') || $data->added_by->id == auth()->user()->id)
        {
            return $data;
        } else {
            return null;
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();

        try {
            $client = Clients::create([
                'company'         => $request->company,
                'name'            => $request->name,
                'email'           => $request->email ?? 'N/A',
                'phone_no'        => $request->phone_no,
                'area'            => $request->area,
                'interest_status' => $request->interest_status ?? 0,
                'product_type'    => $request->product_type ?? 'N/A',
                'client_opinion'  => $request->client_opinion ?? 'N/A',
                'officer_opinion' => $request->officer_opinion ?? 'N/A',
                'added_by'        => auth()->user()->id,
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
            ]);

            if ($request->hasFile('document'))
            {
                $this->uploadDoc($request, $client);
            }

            DB::commit();

            return true;
        }
        catch (QueryException $ex)
        {
            return false;
        }
    }

    public function import(Request $request)
    {
        $file = $request->file('file');

        try {
            Excel::import(new ClientsImport, $file);

            return true;
        }
        catch (\Exception $ex)
        {
            return false;
        }
    }

    public function updateInfo(Request $request, $id)
    {
        $client = Clients::findOrFail($id);

        $client->update([
            'company'         => $request->company,
            'name'            => $request->name,
            'email'           => $request->email,
            'phone_no'        => $request->phone_no,
            'area'            => $request->area,
            'interest_status' => $request->interest_status,
            'product_type'    => $request->product_type,
            'client_opinion'  => $request->client_opinion ?? 'N/A',
            'officer_opinion' => $request->officer_opinion ?? 'N/A',
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
        ]);

        if ($client->confirmation_date)
        {
            if ($request->domain) {
                $website = $client->website;

                if ($website) {
                    $website->domain = $request->domain;
                    $website->save();
                } else {
                    $client->website()->create([
                        'domain' => $request->domain
                    ]);
                }
            }

            if ($request->amount) {
                $payment = $client->payment;

                if ($payment) {
                    $payment->amount = $request->amount;

                    if ($request->payment_type_id) {
                        $payment->payment_type_id = $request->payment_type_id;

                        if (in_array($request->payment_type_id, [2, 3, 4])) {
                            $payment->transaction_id = $request->transaction_id;
                        } else {
                            $payment->transaction_id = null;
                        }
                    }

                    $payment->save();
                }
            }
        }
    }

    public function updateDoc(Request $request, $id)
    {
        $client = Clients::findOrFail($id);

        if($client->document)
        {
            deleteFile($client->document);
        }

        $this->uploadDoc($request, $client);
    }

    private function uploadDoc(Request $request, $client)
    {
        $file = $request->file('document');
        $filename = hexdec(uniqid()). '.' . $file->getClientOriginalExtension();
        $file->move(public_path('/uploads/clients/documents'),$filename);

        $client->document = '/uploads/clients/documents/' . $filename;
        $client->save();
    }

    public function delete(Request $request)
    {
        Clients::whereIn('id', $request->ids)->delete();
    }

    public function getPayableClients()
    {
        return Clients::whereNot('company', 'N/A')
            ->whereNot('email', 'N/A')
            ->whereNotNull('document')
            ->whereNot('product_type', 'N/A')
            ->where('interest_status', 100)
            ->whereNull('confirmation_date')
            ->latest()->get();
    }

    public function fetchAllClient(Request $request)
    {
        return $this->client->clone()
            ->when($request->search, function ($q) use ($request) {
                return $q->where('name','like','%'.$request->search.'%');
            })
            ->orderByDesc('id')
            ->paginate($request->per_page)
            ->appends($request->except('page','per_page'));
    }

}
