<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCategory extends Model
{
    use HasFactory;

    protected $hidden = ['created_at','updated_at'];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_category_id');
    }
}
