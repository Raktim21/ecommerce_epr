<?php

namespace App\Http\Requests;

use App\Models\FoodAllowance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FoodAllowanceStatusRequest extends FormRequest
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
            'status'        => 'required|in:1,2',
            'allowances'    => 'required|array|min:1',
            'allowances.*'  => ['required',
                                function ($attr, $val, $fail) {
                                    $allowance = FoodAllowance::find($val);

                                    if(!$allowance)
                                    {
                                        $fail('Invalid food allowance selected.');
                                    }
                                    else if ($allowance->allowance_status != 0) {
                                        $status = $allowance->allowance_status == 1 ? 'paid.' : 'rejected.';
                                        $fail('Food allowance of '. $allowance->created_by_info->name .' has already been ' . $status);
                                    }
                                }]
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
