<?php

namespace App\Models;

use App\Services\UserPointService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'payment_type_id',
        'payment_category_id',
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

    public function category()
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_category_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            (new UserService)->sendNotification('New payment has been stored for category: '. $payment->category->name .'.', 'client-payment', $payment->client_id);
            (new UserPointService())->savePoints(2);
        });
    }
}
