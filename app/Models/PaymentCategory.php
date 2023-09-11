<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentCategory extends Model
{
    use HasFactory;

    protected $table = 'payment_categories';

    protected $fillable = ['name', 'description', 'price'];

    protected $hidden = ['created_at','updated_at'];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_category_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($cat) {
            Cache::forget('payment_categories');
        });

        static::updated(function ($cat) {
            Cache::forget('payment_categories');
        });

        static::deleted(function ($cat) {
            Cache::forget('payment_categories');
        });
    }
}
