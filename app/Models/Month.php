<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Month extends Model
{
    use HasFactory;

    protected $table = 'months';
    protected $guarded = ['id','name'];

    public function salaries()
    {
        return $this->hasMany(Salary::class, 'month_id');
    }
}
