<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    protected $guarded = [
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_type_id');
    }

    public function clientTransactions()
    {
        return $this->hasMany(ClientTransaction::class, 'payment_type_id');
    }
}
