<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\FollowUpInfo;
use Illuminate\Http\Request;

class FollowUpService
{
    public function store(Request $request)
    {
        $follow = FollowUpInfo::create([
            'client_id' => $request->client_id,
            'detail' => $request->detail,
            'occurred_on' => $request->occurred_on,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        (new UserService)->sendNotification('New client follow-up has been created.', 'follow-up', $follow->id);
    }

    public function show($id)
    {
        return FollowUpInfo::where('client_id', $id)
                ->join('clients', 'clients.id', '=', 'follow_up_infos.client_id')
                ->selectRaw("follow_up_infos.*,
                CASE WHEN ST_Distance_Sphere(
                    point(clients.longitude, clients.latitude),
                    point(follow_up_infos.longitude, follow_up_infos.latitude)
                ) <= 100
                THEN 'VALID'
                ELSE 'INVALID'
                END AS status")->get();
    }

    public function update(Request $request, $id)
    {
        $follow = FollowUpInfo::findOrFail($id)->update([
            'detail' => $request->detail,
            'occurred_on' => $request->occurred_on
        ]);

        (new UserService)->sendNotification("A client's follow-up information has been updated.", 'client', $follow->client_id);
    }

    public function delete($id)
    {
        FollowUpInfo::findOrFail($id)->delete();
    }
}
