<?php

namespace App\Services;

use App\Models\Point;
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
}
