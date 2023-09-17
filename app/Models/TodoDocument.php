<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoDocument extends Model
{
    use HasFactory;

    protected $fillable = ['todo_id', 'document'];

    protected $hidden = ['created_at', 'updated_at'];

    public function todo()
    {
        return $this->belongsTo(Todo::class, 'todo_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($doc) {
            deleteFile($doc->document);
        });
    }
}
