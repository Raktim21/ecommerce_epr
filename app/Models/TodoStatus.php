<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoStatus extends Model
{
    use HasFactory;

    protected $table = 'todo_statuses';

    protected $hidden = ['created_at', 'updated_at'];

    public function todos()
    {
        return $this->hasMany(Todo::class, 'status_id');
    }
}
