<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $table = 'employee_profiles';

    protected $fillable = ['user_id','salary','general_kpi','document','joining_date'];

    protected $hidden = ['updated_at','created_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function salary_data()
    {
        return $this->hasMany(Salary::class, 'employee_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($profile) {
            if($profile->created_at != $profile->user->created_at)
            {
                (new UserService())->sendNotification(
                    'Employee profile has been created for '. $profile->user->name .'.',
                    'user',
                    $profile->user_id);
            }
        });
    }
}
