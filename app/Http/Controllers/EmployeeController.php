<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeSalaryExport;
use App\Http\Requests\EmployeeGetRequest;
use App\Http\Requests\FileTypeRequest;
use App\Http\Requests\StoreSalaryRequest;
use App\Models\EmployeeProfile;
use App\Services\EmployeeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

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

    public function salaryExport(FileTypeRequest $request)
    {
        if ($request->type == 'pdf') {
            $data = $this->service->getAll();

            $pdf = Pdf::loadView('salary', compact('data'));
            return $pdf->stream('salary-data_' . now() . '.pdf');
        } else {
            $file_name = 'employee-salary-list-' . date('dis') . '.' . $request->type;

            return Excel::download(new EmployeeSalaryExport(), $file_name);
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
