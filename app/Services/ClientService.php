<?php

namespace App\Services;

use App\Models\Clients;
use Illuminate\Http\Request;

class ClientService
{
    public function show($id)
    {
        return Clients::with('status_id')
            ->with(['added_by' => function($q) {
                $q->select('id','name');
            }])->findOrFail($id);
    }
    public function create(Request $request)
    {
        $client = Clients::create([
            'company' => $request->company,
            'name' => $request->name,
            'email' => $request->email ?? 'N/A',
            'phone_no' => $request->phone_no,
            'area' => $request->area,
            'status_id' => 1,
            'product_type' => $request->product_type ?? 'N/A',
            'client_opinion' => $request->client_opinion ?? 'N/A',
            'officer_opinion' => $request->officer_opinion ?? 'N/A',
            'added_by' => auth()->user()->id
        ]);

        if ($request->hasFile('document'))
        {
            $this->uploadAvatar($request, $client);
        }
    }

    public function update(Request $request, $id)
    {
        $client = Clients::find($id);

        $client->update([
            'company' => $request->company,
            'name' => $request->name,
            'email' => $request->email,
            'phone_no' => $request->phone_no,
            'area' => $request->area,
            'status_id' => $request->status_id,
            'product_type' => $request->product_type,
            'client_opinion' => $request->client_opinion ?? 'N/A',
            'officer_opinion' => $request->officer_opinion ?? 'N/A',
        ]);
    }

    public function uploadAvatar(Request $request, $client)
    {
        $file = $request->file('document');
        $filename = hexdec(uniqid()). '.' . $file->getClientOriginalExtension();
        $file->move(public_path('/uploads/clients/documents'),$filename);

        $client->document = '/uploads/clients/documents/' . $filename;
        $client->save();
    }

}
