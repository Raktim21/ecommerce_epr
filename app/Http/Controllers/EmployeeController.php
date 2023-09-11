<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeGetRequest;
use App\Http\Requests\StoreSalaryRequest;
use App\Models\EmployeeProfile;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    private $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    public function getEmployeeList(EmployeeGetRequest $request)
    {
        $data = $this->service->getAll();

        return response()->json([
            'success' => true,
            'data'    => $data
        ], count($data)==0 ? 204 : 200);
    }

    public function updateEmployeeActive(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $employee = EmployeeProfile::find($id);
            $employee->is_active = $request->is_active;
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'EmployeeProfile has been updated.'
            ],200);

        } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'message' => 'EmployeeProfile not found.'
            ], 404);
        }

    }


    public function storeSalary(StoreSalaryRequest $request)
    {
        if($this->service->giveSalary($request)) {
            return response()->json(['success'=>true], 201);
        } else {
            return response()->json(['success'=>false], 500);
        }
    }
}
