<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\AssignUsersRequest;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\RolePermissionService;

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

    public function permissionList()
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->permissions(),
        ]);
    }

    public function getRole($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->role($id),
        ]);
    }

    public function createRole(CreateRoleRequest $request)
    {
        $this->service->createRole($request);

        return response()->json([
            'success'  => true,
        ], 201);
    }

    public function updateRole(UpdateRoleRequest $request, $id)
    {
        if($this->service->updateRole($request, $id))
        {
            return response()->json([
                'success'  => true,
            ]);
        }
        return response()->json([
            'success' => false,
            'error'   => 'The selected role can not be updated.'
        ], 422);
    }

    public function assignRole(AssignRoleRequest $request, $user_id)
    {
        $this->service->assignUser($request, $user_id);

        return response()->json([
            'success'  => true,
        ], 201);
    }

    public function assignUsers(AssignUsersRequest $request)
    {
        if($this->service->assignMultipleUser($request))
        {
            return response()->json([
                'success'  => true,
            ], 201);
        }
        return response()->json([
            'success'  => false,
        ], 500);
    }

    public function deleteRole($id)
    {
        if($this->service->deleteRole($id))
        {
            return response()->json([
                'success'  => true,
            ]);
        }
        return response()->json([
            'success' => false,
            'error'   => 'The selected role can not be deleted.'
        ], 422);
    }
}
