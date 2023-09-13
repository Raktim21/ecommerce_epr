<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = ['name', 'description', 'price'];

    protected $hidden = ['created_at','updated_at'];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'service_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($cat) {
            Cache::forget('services');
        });

        static::updated(function ($cat) {
            Cache::forget('services');
        });
    }
}
