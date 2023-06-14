<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\FollowUpInfo;
use Illuminate\Http\Request;

class FollowUpService
{
    public function store(Request $request)
    {
        FollowUpInfo::create([
            'client_id' => $request->client_id,
            'detail' => $request->detail,
            'occurred_on' => $request->occurred_on
        ]);
    }

    public function show($id)
    {
        return Clients::findOrFail($id)->follow_ups;
    }

    public function update(Request $request, $id)
    {
        FollowUpInfo::findOrFail($id)->update([
            'detail' => $request->detail,
            'occurred_on' => $request->occurred_on
        ]);
    }

    public function delete($id)
    {
        FollowUpInfo::findOrFail($id)->delete();
    }
}
