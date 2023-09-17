<?php

namespace App\Models;

use App\Notifications\AdminNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoUser extends Model
{
    use HasFactory;

    protected $table = 'todo_users';

    protected $fillable = ['todo_id', 'user_id'];

    public function todo()
    {
        return $this->belongsTo(Todo::class, 'todo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($todo) {
            $todo->user->notify(new AdminNotification(
                auth()->user()->name . ' has assigned you to a new task.',
                '/todo'));
        });

        static::deleted(function ($todo) {
            $todo->user->notify(new AdminNotification(
                'You are no longer assigned to the task titled as: '. $todo->todo->title .'.',
                '/todo'));
        });
    }
}
