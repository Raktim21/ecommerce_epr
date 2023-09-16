<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoStoreRequest;
use App\Models\Todo;
use App\Models\TodoDocument;
use App\Models\TodoUser;
use App\Services\TodoService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

        return response()->json([
            'success'   => true
        ]);
    }

    public function addDocuments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|mimes:jpg,png,jpeg,xlsx,csv,pdf,doc,docx|max:2048',
            'todo_id'  => ['required', 'integer',
                            function($attr, $val, $fail) {
                                if(!auth()->user()->hasRole('Super Admin') &&
                                TodoUser::where('user_id', auth()->user()->id)->where('todo_id', $val)->doesntExist())
                                {
                                    $fail('You cannot add document to this task.');
                                }
                            }]
        ]);

        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'error'   => $validator->errors()->first()
            ], 422);
        }

        try {
            $doc = $request->file('document');
            $doc_name = $doc->getClientOriginalName();
            $doc->move(public_path('/uploads/todo/'), $doc_name);

            TodoDocument::create([
                'todo_id'   => $request->todo_id,
                'document'  => '/uploads/todo/' . $doc_name
            ]);

            return response()->json(['success' => true], 201);
        } catch (QueryException $ex)
        {
            return response()->json([
                'success' => false,
                'error'   => $ex->getCode() == 23000 ? 'Document with same name already exists.' : $ex->getMessage()
            ], 422);
        }

    }

    public function addUsers(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => ['required','integer',
                            function($attr, $val, $fail) use ($id) {
                                $invalid_user = TodoUser::where('user_id', $val)->where('todo_id', $id)->first();

                                if($invalid_user)
                                {
                                    $fail($invalid_user->user->name . ' has already assigned to this task.');
                                }
                            }],

        ]);

        if($validator->fails())
        {
            return response()->json([
                'success'   => false,
                'error'     => $validator->errors()->first()
            ], 422);
        }

        $todo = Todo::findOrFail($id);

        if($todo->status_id == 4 || $todo->status_id == 5)
        {
            $status = $todo->status_id == 4 ? 'completed.' : 'cancelled.';

            return response()->json([
                'success'   => false,
                'error'     => 'Cannot assign a task to user that has been ' . $status
            ], 400);
        }

        $todo->assignees()->create([
            'user_id'   => $request->user_id
        ]);

        return response()->json(['success' => true], 201);
    }

    public function removeUser($id)
    {
        $todo = TodoUser::findOrFail($id);

        $status = $todo->status_id == 4 ? 'completed.' : 'cancelled.';

        if($todo->status_id == 4 || $todo->status_id == 5)
        {
            return response()->json([
                'success'   => false,
                'error'     => 'Cannot remove users from a task that has been ' . $status
            ], 400);
        }

        $todo->delete();

        return response()->json(['success' => true]);
    }
}