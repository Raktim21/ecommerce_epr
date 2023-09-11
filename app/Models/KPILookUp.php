<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class KPILookUp extends Model
{
    use HasFactory;

    protected $table = 'kpi_look_ups';

    protected $fillable = ['client_count','amount','per_client_amount'];

    protected $hidden = ['created_at','updated_at'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($kpi) {
            Cache::forget('kpi_look_up');
        });

        static::updated(function ($kpi) {
            Cache::forget('kpi_look_up');
        });

        static::deleted(function ($kpi) {
            Cache::forget('kpi_look_up');
        });
    }
}
