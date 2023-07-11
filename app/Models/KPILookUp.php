<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KPILookUp extends Model
{
    use HasFactory;

    protected $table = 'kpi_look_ups';

    protected $fillable = ['category','client_count','amount','per_client_count'];

    protected $hidden = ['created_at','updated_at'];
}
