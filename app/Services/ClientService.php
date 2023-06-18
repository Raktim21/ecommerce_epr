<?php

namespace App\Services;

use App\Imports\ClientsImport;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ClientService
{
    public function getAll(Request $request)
    {
        $search = $request->search ?? '';
        $status = $request->confirmed ?? '';
        $limit = $request->per_page;

        return Clients::
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
            ->latest()
            ->paginate($limit)
            ->appends($request->except('page','per_page'));
    }

    public function show($id)
    {
        return Clients::with(['added_by' => function($q) {
                $q->select('id','name');
            }])->findOrFail($id);
    }

    public function unpaidClients()
    {
        return Clients::where('confirmation_date',null)->where('interest_status',100)
            ->whereNotNull('document')->whereNot('company','N/A')->whereNot('name','N/A')
            ->whereNot('phone_no','N/A')
            ->whereNot('email','N/A')
            ->select('id','company','name','phone_no','email','area','product_type','status_id','document')
            ->get();
    }

    public function create(Request $request)
    {
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
            Log::info($ex->getMessage());
            return false;
        }
    }

    public function updateInfo(Request $request, $id)
    {
        $client = Clients::find($id);

        $client->update([
            'company' => $request->company,
            'name' => $request->name,
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
    }

    public function updateDoc(Request $request, $id)
    {
        $client = Clients::find($id);

        if($client->document)
        {
            if(File::exists(public_path($client->document)))
            {
                File::delete(public_path($client->document));
            }
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

}
