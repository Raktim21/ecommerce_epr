<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomBill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function client()
    {
        return $this->hasOne(CustomBillClient::class, 'custom_bill_id');
    }

    public function items()
    {
        return $this->hasMany(CustomBillItem::class, 'custom_bill_id');
    }
}
