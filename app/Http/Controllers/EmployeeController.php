<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalaryRequest;
use App\Services\EmployeeService;
use Illuminate\Http\Request;

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

    public function storeSalary(StoreSalaryRequest $request)
    {
        if($this->service->giveSalary($request)) {
            return response()->json(['success'=>true], 201);
        } else {
            return response()->json(['success'=>false], 500);
        }

    }
}
