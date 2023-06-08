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
        'status_id',
        'client_opinion',
        'officer_opinion',
        'document',
        'added_by',
        'confirmation_date'
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
        return $this->hasOne(Payment::class, 'client_id');
    }

    public function status_id()
    {
        return $this->belongsTo(InterestStatus::class, 'status_id');
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
