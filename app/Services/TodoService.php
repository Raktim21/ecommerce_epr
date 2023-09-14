<?php

namespace App\Services;

use App\Models\Todo;
use App\Models\TodoStatus;
use App\Models\TodoUser;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TodoService
{

    public function assignTask(Request $request)
    {
        DB::beginTransaction();

        try {
            $todo = Todo::create([
                'added_by'          => auth()->user()->id,
                'title'             => $request->title,
                'detail'            => $request->detail,
                'priority_level'    => $request->priority_level
            ]);

            foreach ($request->users as $user)
            {
                TodoUser::create([
                    'todo_id'   => $todo->id,
                    'user_id'   => $user
                ]);
            }

            DB::commit();
            return true;
        } catch (QueryException $ex)
        {
            DB::rollback();
            return false;
        }
    }

    public function getStatuses()
    {
        return TodoStatus::whereNot('id', 1)->orderBy('id')->get();
    }

    public function getAll()
    {
        return TodoStatus::with(['todos' => function($q) {
            return $q->when(!auth()->user()->hasRole('Super Admin'), function ($q2) {
                return $q2->whereHas('assignees', function ($q1) {
                    return $q1->where('user_id', auth()->user()->id);
                });
            })
            ->with('assignees')->with(['assigned_by' => function($q1) {
                return $q1->select('id','name','email','avatar');
            }])->orderByDesc('id');
        }])->withCount('todos')
            ->get();
    }
}
