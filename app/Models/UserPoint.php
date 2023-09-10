<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class UserPoint extends Model
{
    use HasFactory;

    protected $table = 'user_points';

    protected $fillable = ['user_id','point_id','points'];

    protected $hidden = ['updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function point_detail()
    {
        return $this->belongsTo(Point::class, 'point_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($point) {
            Cache::forget('auth_profile'.$point->user_id);
        });
    }
}
