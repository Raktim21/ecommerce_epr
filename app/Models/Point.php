<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Point extends Model
{
    use HasFactory;

    protected $table = 'points';

    protected $guarded = ['type'];

    protected $fillable = ['point'];

    protected $hidden = ['created_at','updated_at'];

    public function users()
    {
        return $this->hasMany(UserPoint::class, 'point_id');
    }

    public static function boot()
    {
        parent::boot();

        static::updated(function ($point) {
            Cache::forget('point_types');
        });
    }
}
