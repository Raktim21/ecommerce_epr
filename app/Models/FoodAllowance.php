<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodAllowance extends Model
{
    use HasFactory;

    protected $table = 'food_allowances';

    protected $hidden = ['created_at','updated_at'];

    protected $fillable = [
        'lat','lng','address','amount','note','document','occurred_on','created_by','client_id','follow_up_id','allowance_status'
    ];

    public function created_by_info()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function follow_up()
    {
        return $this->belongsTo(FollowUpInfo::class, 'follow_up_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($allowance) {
            (new UserService)->sendNotification(
                $allowance->created_by_info->name . ' has posted a new request for food allowance.',
                'food-allowance',
                $allowance->id);
        });
    }
}
