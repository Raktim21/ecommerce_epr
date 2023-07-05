<?php

namespace App\Models;

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
        'created_at'
    ];

    public function follow_ups()
    {
        return $this->hasMany(FollowUpInfo::class, 'client_id');
    }

    public function payment()
    {
        return $this->hasMany(Payment::class, 'client_id');
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
}
