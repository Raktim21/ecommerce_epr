<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Services\UserService;

class UserController extends Controller
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getAll()
        ]);
    }

    public function store(UserStoreRequest $request)
    {
//        if($this->service->store($request))
//        {
            return response()->json([
                'success' => true,
            ], 201);
//        } else {
//            return response()->json([
//                'success' => false,
//                'error'   => 'Something went wrong.'
//            ], 500);
//        }
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $this->service->updateUser($request, $id);

        return response()->json([
            'success' => true,
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->get($id),
            'role' => $this->service->getRole($id)[0] ?? null
        ]);
    }

    public function destroy($id)
    {
        if($this->service->delete($id))
        {
            return response()->json([
                'success' => true,
            ]);
        }
        return response()->json([
            'success' => false,
            'error' => 'This user can not be deleted.'
        ], 422);
    }
}
