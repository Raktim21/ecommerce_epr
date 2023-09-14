<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoStoreRequest;
use App\Models\Todo;
use App\Models\TodoUser;
use App\Services\TodoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ToDoController extends Controller
{
    protected $service;

    public function __construct(TodoService $service)
    {
        $this->service = $service;
    }

    public function getStatuses()
    {
        $data = Cache::rememberForever('todo_statuses', function () {
            return $this->service->getStatuses();
        });

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

    public function store(TodoStoreRequest $request)
    {
        if($this->service->assignTask($request))
        {
            return response()->json(['success' => true], 201);
        } else {
            return response()->json([
                'success'   => false,
                'error'     => 'Something went wrong.'
            ], 500);
        }
    }

    public function index()
    {
        $data = $this->service->getAll();

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

    public function updateInfo(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'             => ['required'],
            'detail'            => 'nullable|string|max:498',
            'priority_level'    => 'required|in:1,2,3'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'error'   => $validator->errors()->first()
            ], 422);
        }

        Todo::find($id)->update([
            'title' => $request->title,
            'detail' => $request->detail,
            'priority_level' => $request->priority_level
        ]);
    }

    public function addUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => ['required',
                            function($attr, $val, $fail) use ($id) {
                                $invalid_user = TodoUser::where('user_id', $val)->where('todo_id', $id)->first();

                                if($invalid_user)
                                {
                                    $fail('The selected user has already assigned to this task.');
                                }
                            }]
        ]);

        if($validator->fails())
        {
            return response()->json([
                'success'   => false,
                'error'     => $validator->errors()->first()
            ], 422);
        }

        TodoUser::create([
            'todo_id'   => $id,
            'user_id'   => $request->user_id
        ]);

        return response()->json(['success' => true], 201);
    }

    public function removeUser($id)
    {
        TodoUser::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
