<?php

namespace App\Http\Requests;

use App\Models\TransportAllowance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransportAllowanceStatusRequest extends FormRequest
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
            'allowances.*'  => ['required', 'exists:transport_allowances,id',
                                function ($attr, $val, $fail) {
                                    $allowance = TransportAllowance::find($val);

                                    if(!$allowance)
                                    {
                                        $fail('Invalid transport allowance selected.');
                                    }
                                    else {
                                        if (!$allowance->end_time)
                                        {
                                            $fail('Status of transport allowance of '. $allowance->created_by_info->name .' cannot be changed.');
                                        }
                                        else if ($allowance->allowance_status != 0)
                                        {
                                            $status = $allowance->allowance_status == 1 ? 'paid.' : 'rejected.';
                                            $fail('Transport allowance of '. $allowance->created_by_info->name .' has already been ' . $status);
                                        }
                                    }
                                }]
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.'
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
