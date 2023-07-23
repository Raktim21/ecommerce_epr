<?php

namespace App\Http\Requests;

use App\Models\Salary;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'employees'         => 'required|array|min:1',
            'employees.*'       => ['required','exists:employees,id',
                                    function($attr, $val, $fail) {
                                        $existing = Salary::where('employee_id', $val)
                                            ->where('year_name', $this->input('year_name'))
                                            ->where('month_id', $this->input('month_id'))
                                            ->first();

                                        if($existing && $this->input('pay_status') == 1) {
                                            $fail('Both allowance and salary can not be paid.');
                                        }
                                        else if($existing && $existing->pay_status == $this->input('pay_status')) {
                                            $fail('Salary has already been given for the given type.');
                                        }
                                        else if($existing && $existing->pay_status == 1) {
                                            $fail('Both allowance and salary have been paid for the selected month.');
                                        }


                                    }],
            'year_name'         => 'required|date_format:Y|before:' . (date('Y') + 1),
            'month_id'          => 'required|exists:months,id',
            'pay_status'        => 'required|in:1,2,3' // 1: both allowance and salary, 2: only salary 3: only allowance
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
