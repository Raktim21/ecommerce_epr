<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
