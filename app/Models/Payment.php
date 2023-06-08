<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'amount'
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }
}
