<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\FollowUpInfo;
use Illuminate\Http\Request;

class FollowUpService
{
    public function store(Request $request)
    {
        $followup = FollowUpInfo::create([
            'client_id' => $request->client_id,
            'detail' => $request->detail,
            'occurred_on' => $request->occurred_on,
            
        ]);

        $followup->latitude = $request->latitude;
        $followup->longitude = $request->longitude;
        $followup->save();

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
        
        
        
        
        // Clients::findOrFail($id)->follow_ups()
        //                 ->join('clients', 'clients.id', '=', 'follow_up_infos.client_id')
        //                 ->selectRaw("
        //                     clients.*, 
        //                     CASE WHEN ST_Distance_Sphere(
        //                         point(clients.longitude, clients.latitude), 
        //                         point(follow_up_infos.longitude, follow_up_infos.latitude)
        //                     ) <= 100 
        //                     THEN 'VALID' 
        //                     ELSE 'INVALID' 
        //                     END AS status
        //                 ")
        //                 ->get();
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
