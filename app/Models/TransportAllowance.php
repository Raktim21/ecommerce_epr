<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportAllowance extends Model
{
    use HasFactory;

    protected $table = 'transport_allowances';

    protected $fillable = [
        'from_lat','from_lng','from_address','start_time','to_lat','to_lng',
        'to_address','end_time','transport_type','amount','document','note',
        'visit_type','created_by','client_id','follow_up_id','allowance_status',
        'travel_status'
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function created_by_info()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function follow_up()
    {
        return $this->belongsTo(FollowUpInfo::class, 'follow_up_id');
    }
}
