<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'detail',
        'occurred_on',
        'latitude',
        'longitude'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function allowances()
    {
        return $this->hasMany(TransportAllowance::class, 'follow_up_id');
    }
}
