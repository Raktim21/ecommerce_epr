<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = ['user_id','salary','document','joining_date'];

    protected $hidden = ['updated_at','created_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function salary_data()
    {
        return $this->hasMany(Salary::class, 'employee_id');
    }
}
