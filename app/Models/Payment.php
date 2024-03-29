<?php

namespace App\Models;

use App\Services\UserPointService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            $payment->invoice_no = 'PAY-' . rand(100,999) . '-' . time();
        });

        static::created(function ($payment) {
            (new UserService)->sendNotification(
                'New payment has been stored for service: '. $payment->service->name .'.',
                '/confirm-client/'.$payment->client_id);

            (new UserPointService())->savePoints(2, $payment->client->added_by);

            $payment->client->update([
                'confirmation_date' => Carbon::now('Asia/Dhaka')
            ]);
        });
    }
}
