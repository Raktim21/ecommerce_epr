<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FollowUpInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'detail',
        'occurred_on',
        'latitude',
        'longitude'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function allowances()
    {
        return $this->hasMany(TransportAllowance::class, 'follow_up_id');
    }

    public function food_allowances()
    {
        return $this->hasMany(FoodAllowance::class, 'follow_up_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($follow) {
            (new UserService)->sendNotification(auth()->user()->name . 'has created a new client follow-up.', 'follow-up', $follow->id);
            Cache::forget('client_follow_up'.$follow->client_id);
        });

        static::updated(function ($follow) {
            Cache::forget('client_follow_up'.$follow->client_id);
        });

        static::deleted(function ($follow) {
            Cache::forget('client_follow_up'.$follow->client_id);
        });
    }
}
