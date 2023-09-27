<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\FollowUpInfo;
use App\Models\FollowUpReminder;
use Illuminate\Http\Request;

class FollowUpService
{
    public function store(Request $request)
    {
        FollowUpInfo::create([
            'client_id' => $request->client_id,
            'detail' => $request->detail,
            'occurred_on' => $request->occurred_on,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);
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

    public function update(Request $request, $id): bool
    {
        $follow = FollowUpInfo::findOrFail($id);

        if (!$follow->client->confirmation_date){
            $follow->update([
                'detail' => $request->detail,
                'occurred_on' => $request->occurred_on
            ]);

            return true;
        }
        return false;
    }

    public function delete($id)
    {
        FollowUpInfo::findOrFail($id)->delete();
    }

    public function storeReminder(Request $request)
    {
        FollowUpReminder::create([
            'client_id'             => $request->client_id,
            'followup_session'      => $request->followup_session,
            'notes'                 => $request->notes,
            'added_by'              => auth()->user()->id
        ]);
    }

    public function getPendingFollowUps()
    {
        return FollowUpReminder::with(['client' => function($q) {
            return $q->select('id','name','email','phone_no');
        }])->when(auth()->user()->hasRole('Super Admin'), function ($q) {
            return $q->with(['added_by_info' => function($q) {
                return $q->select('id','name','email','phone','avatar');
            }]);
        })->when(!auth()->user()->hasRole('Super Admin'), function ($q) {
            return $q->where('added_by', auth()->user()->id);
        })->where('followup_session', '>', date('Y-m-d H:i:s'))
            ->orderBy('id')->get();
    }
}
