<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['updated_at'];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function services()
    {
        return $this->hasMany(BillProduct::class, 'bill_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($bill) {
            (new UserService)->sendNotification(
                auth()->user()->name . ' has created new bill for '. $bill->client->name .'.',
                'client-bill',
                $bill->id);
        });
    }
}
