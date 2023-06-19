<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        $this->service->store($request);

        return response()->json([
            'success' => true,
        ], 201);
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
            'data' => $this->service->get($id)
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
        ], 500);
    }
}
