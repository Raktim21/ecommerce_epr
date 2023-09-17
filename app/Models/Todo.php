<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $table = 'todos';

    protected $guarded = ['id'];

    protected $hidden = ['updated_at'];

    public function status()
    {
        return $this->belongsTo(TodoStatus::class, 'status_id');
    }

    public function assigned_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'todo_users', 'todo_id');
    }

    public function documents()
    {
        return $this->hasMany(TodoDocument::class, 'todo_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($todo) {
            (new UserService())->sendNotification(
                auth()->user()->name . ' has assigned a new task to users.',
                '/todo');
        });

        static::updated(function ($todo) {
            if ($todo->status_id > 3)
            {
                $status = $todo->status_id == 4 ? 'completed.' : 'cancelled.';

                (new UserService())->sendNotification(
                    auth()->user()->name . ' has marked a task as ' . $status,
                    '/todo'
                );
            }
        });
    }
}
