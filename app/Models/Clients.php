<?php

namespace App\Models;

use App\Services\UserPointService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;

    protected $fillable = [
        'company',
        'name',
        'email',
        'phone_no',
        'area',
        'interest_status',
        'product_type',
        'client_opinion',
        'officer_opinion',
        'document',
        'added_by',
        'confirmation_date',
        'latitude',
        'longitude'
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function follow_ups()
    {
        return $this->hasMany(FollowUpInfo::class, 'client_id');
    }

    public function follow_up_reminders()
    {
        return $this->hasMany(FollowUpReminder::class, 'client_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'client_id');
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function website()
    {
        return $this->hasOne(Website::class, 'client_id');
    }

    public function allowance()
    {
        return $this->hasMany(TransportAllowance::class, 'client_id');
    }

    public function food_allowance()
    {
        return $this->hasMany(FoodAllowance::class, 'client_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($client) {
            (new UserPointService())->savePoints(1, auth()->user()->id);
            (new UserService)->sendNotification(
                auth()->user()->name . ' has created a client profile for '. $client->name .'.',
                'client',
                $client->id);
        });

        static::updated(function ($client) {
            if(is_null($client->confirmation_date))
            {
                (new UserService)->sendNotification(
                    'Client profile of '. $client->name .' has been updated.',
                    'client',
                    $client->id);
            }
        });
    }
}
