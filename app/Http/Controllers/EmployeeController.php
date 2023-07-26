<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalaryRequest;
use App\Models\Employee;
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

    public function getEmployeeList()
    {
        $data = $this->service->getAll();

        return response()->json([
            'success' => true,
            'data'    => $data
        ], count($data)==0 ? 204 : 200);
    }




    public function updateEmployeeInfo(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'salary'      => 'required|numeric|min:0',
            'general_kpi' => 'required|numeric|min:0',
            'document'    => 'nullable|file|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $employee = Employee::find($id);
            $employee->salary = $request->salary;
            $employee->general_kpi = $request->general_kpi;
            $employee->save();

            if ($request->hasFile('document')) {
                saveImage($request->file('document'), '/uploads/users/document/', $employee, 'document');
            }

            return response()->json([
                'success' => true,
                'message' => 'Employee has been updated.'
            ],200);
            
        } catch (\Throwable $th) {
            
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.'
            ], 304);
        }

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
            $employee = Employee::find($id);
            $employee->is_active = $request->is_active;
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Employee has been updated.'
            ],200);
            
        } catch (\Throwable $th) {
            
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.'
            ], 304);
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
