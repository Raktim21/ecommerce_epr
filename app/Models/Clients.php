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

    public function status()
    {
        $this->belongsTo(InterestStatus::class, 'status_id');
    }

    public function added_by_admin()
    {
        $this->belongsTo(User::class, 'added_by');
    }
}
