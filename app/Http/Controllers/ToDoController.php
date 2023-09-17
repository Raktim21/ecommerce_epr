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
        $todo = Todo::findOrFail($id);

        if($todo->status_id > 3)
        {
            return response()->json([
                'success' => false,
                'error'   => 'This task is not updatable.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'title'             => ['required'],
            'detail'            => 'nullable|string|max:498',
            'priority_level'    => 'required|in:1,2,3',
            'deadline'          => ['required','date_format:Y-m-d H:i','after:'.date('Y-m-d H:i')],
        ]);

        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'error'   => $validator->errors()->first()
            ], 422);
        }

        $todo->update([
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
                                $todo = Todo::find($val);

                                if(!$todo || $todo->status_id > 3)
                                {
                                    $fail('You cannot add document to this task.');
                                }
                                if(!auth()->user()->hasRole('Super Admin') &&
                                    $todo->assignees()->where('user_id', auth()->user()->id)->doesntExist())
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

            $todo_doc = TodoDocument::create([
                'todo_id'   => $request->todo_id,
                'document'  => '/uploads/todo/' . $doc_name
            ]);

            return response()->json([
                'success' => true,
                'data'    => $todo_doc
            ], 201);
        } catch (QueryException $ex)
        {
            return response()->json([
                'success' => false,
                'error'   => $ex->getCode() == 23000 ? 'Document with same name already exists.' : $ex->getMessage()
            ], 422);
        }
    }

    public function deleteDocument($id)
    {
        $doc = TodoDocument::findOrFail($id);

        if(!auth()->user()->hasRole('Super Admin') &&
            TodoUser::where('todo_id', $doc->todo_id)->where('user_id', auth()->user()->id)->doesntExist())
        {
            return response()->json([
                'success' => false,
                'error'   => 'You are not allowed to perform this action.'
            ], 403);
        }

        if($doc->todo->status_id == 4 || $doc->todo->status_id == 5)
        {
            $status = $doc->todo->status_id == 4 ? 'completed.' : 'cancelled.';
            return response()->json([
                'success' => false,
                'error'   => 'Document cannot be removed when task has been ' . $status
            ], 400);
        }

        $doc->delete();

        return response()->json(['success' => true]);
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

        TodoUser::create([
            'todo_id'   => $id,
            'user_id'   => $request->user_id
        ]);

        return response()->json(['success' => true], 201);
    }

    public function removeUser($task_id, $user_id)
    {
        $todo = TodoUser::where('todo_id', $task_id)->where('user_id', $user_id)->first();

        if(!$todo)
        {
            return response()->json([
                'success'   => false,
                'error'     => 'This user is not present in the assignee list.'
            ], 400);
        }

        $status = $todo->todo->status_id == 4 ? 'completed.' : 'cancelled.';

        if($todo->todo->status_id > 3)
        {
            return response()->json([
                'success'   => false,
                'error'     => 'Cannot remove users from a task that has been ' . $status
            ], 400);
        }

        $todo->delete();

        return response()->json(['success' => true]);
    }

    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status'    => 'required|in:right,left'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'error'   => $validator->errors()->first()
            ], 422);
        }

        $todo = Todo::findOrFail($id);

        if($todo->status_id == 1 && $request->status == 'left')
        {
            return response()->json([
                'success' => false,
                'error'   => 'Invalid status.'
            ], 400);
        }

        if($todo->status_id > 3)
        {
            return response()->json([
                'success' => false,
                'error'   => 'Status of this task cannot be changed now.'
            ], 400);
        }

        $exist = $todo->assignees()->where('user_id', auth()->user()->id)->first();

        if(auth()->user()->hasRole('Super Admin') || $exist)
        {
            if ($request->status == 'right') {
                $todo->status_id += 1;
            } else {
                $todo->status_id -= 1;
            }

            $todo->save();

            return response()->json(['success' => true]);
        } else {
            return response()->json([
                'success' => false,
                'error'   => 'You are not allowed to update the information.'
            ], 403);
        }
    }

    public function changeAdminStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status'    => 'required|in:4,5'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'error'   => $validator->errors()->first()
            ], 422);
        }

        $todo = Todo::findOrFail($id);

        if($todo->status_id != 3)
        {
            return response()->json([
                'success' => false,
                'error'   => 'Status of this task cannot be changed.'
            ], 400);
        }

        $todo->update([
            'status_id' => $request->status
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteTodo($id)
    {
        $status = $this->service->deleteTask($id);

        if ($status == 'done')
        {
            return response()->json(['success' => true]);
        }
        else {
            return response()->json([
                'success' => false,
                'error'   => 'Task cannot be deleted when it is ' . $status . '.'
            ], 400);
        }
    }
}
