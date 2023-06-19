<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'payment_type_id',
        'transaction_id',
        'invoice_no',
        'amount'
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function type()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }
}
