<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeProfileStoreRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $data = $this->service->getAll($request);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(UserStoreRequest $request)
    {
        if($this->service->store($request))
        {
            return response()->json([
                'success' => true,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'error'   => 'Something went wrong.'
            ], 500);
        }
    }

    public function createProfile(EmployeeProfileStoreRequest $request)
    {
        $this->service->storeProfile($request);

        return response()->json(['success' => true], 201);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        if($this->service->updateUser($request, $id)) {
            return response()->json([
                'success' => true,
            ]);
        }
        return response()->json([
            'success' => false,
            'error' => 'Something went wrong.'
        ], 500);
    }

    public function show($id)
    {
        $data = $this->service->get($id);

        return response()->json([
            'success' => true,
            'data' => $data,
            'role' => $this->service->getRole($id)[0] ?? null
        ], is_null($data) ? 204 : 200);
    }

    public function changeStatus($id)
    {
        if($this->service->activeStatus($id))
        {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'This user can not be deactivated.'
        ], 400);
    }
}
