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
        return response()->json([
            'success' => true,
            'data'    => $this->service->getAll()
        ]);
    }

    public function storeSalary(StoreSalaryRequest $request)
    {}
}
