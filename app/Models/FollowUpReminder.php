<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FollowUpReminder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $hidden = ['updated_at'];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function added_by_info()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($reminder) {
            Cache::forget('auth_profile'.$reminder->added_by);
        });
    }
}
