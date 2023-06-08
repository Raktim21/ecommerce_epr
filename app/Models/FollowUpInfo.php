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
        'occurred_on'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }
}
