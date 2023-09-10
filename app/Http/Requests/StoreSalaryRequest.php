<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreSalaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'year_name'         => 'required|date_format:Y|before:' . (date('Y') + 1),
            'month_id'          => ['required','exists:months,id',
                function($attr, $val, $fail) {
                    if($this->input('year_name') == date('Y')) {
                        if($val > date('n')) {
                            $fail('Salary for future can not be given.');
                        }
                    }
                }],
            'employees'         => 'required|array|min:1',
            'employees.*'       => ['required','exists:employees,id',
                                    function($attr, $val, $fail) {
                                        $existing = Salary::where('employee_id', $val)
                                            ->where('year_name', $this->input('year_name'))
                                            ->where('month_id', $this->input('month_id'))
                                            ->first();

                                        $employee = Employee::find($val);

                                        $date = Carbon::create($this->input('year_name'), $this->input('month_id'), 1);

                                        if($existing) {
                                            $fail('Salary has already been given to '. $existing->employee->user->name .' for the given month.');
                                        }
                                        else if($employee && $employee->joining_date > $date) {
                                            $fail($employee->user->name .' has joined after the selected time.');
                                        }
                                    }],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'  => false,
            'error'  => $validator->errors()->first(),
        ], 422));
    }
}
