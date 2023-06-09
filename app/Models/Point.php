<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
