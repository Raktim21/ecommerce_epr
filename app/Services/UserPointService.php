<?php

namespace App\Services;

use App\Models\Point;
use App\Models\UserPoint;
use Illuminate\Http\Request;

class UserPointService
{
    public function getTypes()
    {
        return Point::latest()->get();
    }

    public function update(Request $request, $id)
    {
        Point::findOrFail($id)->update([
            'point' => $request->point
        ]);
    }

    public function savePoints($point_id)
    {
        UserPoint::create([
            'user_id'   => auth()->user()->id,
            'point_id'  => $point_id,
            'points'    => Point::find($point_id)->point
        ]);
    }
}
