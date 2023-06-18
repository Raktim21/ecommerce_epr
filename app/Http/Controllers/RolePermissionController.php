<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRoleRequest;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    private $service;

    public function __construct(RolePermissionService $service)
    {
        $this->service = $service;
    }

    public function roleList()
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->roles(),
        ]);
    }

    public function assignRole(AssignRoleRequest $request, $user_id)
    {
        $this->service->assignUser($request, $user_id);

        return response()->json([
            'success'  => true,
        ], 201);
    }

    public function assignUsers(AssignUsersRequest $request)
    {}
}
