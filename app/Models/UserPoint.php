<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    use HasFactory;

    protected $table = 'user_points';

    protected $fillable = ['user_id','point_id','points'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function point_detail()
    {
        return $this->belongsTo(Point::class, 'point_id');
    }
}
