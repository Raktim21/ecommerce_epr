<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'address',
        'details',
        'password_reset_token',
        'password_reset_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'password_reset_code',
        'password_reset_token',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function point_list()
    {
        return $this->hasMany(UserPoint::class, 'user_id');
    }

    public function allowances()
    {
        return $this->hasMany(TransportAllowance::class, 'created_by');
    }

    public function food_allowances()
    {
        return $this->hasMany(FoodAllowance::class, 'created_by');
    }

    public function clients()
    {
        return $this->hasMany(Clients::class, 'added_by');
    }

    public function follow_up_reminders()
    {
        return $this->hasMany(FollowUpReminder::class, 'added_by');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            (new UserService())->sendNotification(
                'A new user profile has been created for '. $user->name .'.',
                'user',
                $user->id);
        });

        static::updated(function ($user) {
            Cache::forget('auth_profile'.$user->id);
        });
    }
}
