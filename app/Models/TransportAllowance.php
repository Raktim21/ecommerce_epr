<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportAllowance extends Model
{
    use HasFactory;

    protected $table = 'transport_allowances';

    protected $fillable = [
        'from_lat','from_lng','from_address','start_time','to_lat','to_lng',
        'to_address','end_time','transport_type','amount','document','note',
        'visit_type','created_by','client_id','follow_up_id','allowance_status'
    ];

    protected $hidden = ['updated_at','created_at'];

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function created_by_info()
    {
        return $this->belongsTo(User::class, 'created_by');
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
                $allowance->created_by_info->name . ' has recently started journey for a client follow up.',
                '/ta');
        });

        static::updated(function ($allowance) {
            if($allowance->allowance_status != 0)
            {
                $status = $allowance->allowance_status == 1 ? 'paid.' : 'rejected.';

                (new UserService)->sendNotification(
                    'Transport allowance of '. $allowance->created_by_info->name .' has been ' . $status,
                    '/ta');
            } else{
                (new UserService)->sendNotification(
                    'Transport allowance information of '. $allowance->created_by_info->name .' has been updated.',
                    '/ta');
            }
        });
    }
}
