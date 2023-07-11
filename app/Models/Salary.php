<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $table = 'salaries';

    protected $fillable = ['employee_id','year_name','month_id','payable_amount','paid_amount','incentive_paid'];

    public function month()
    {
        return $this->belongsTo(Month::class, 'month_id');
    }
}
