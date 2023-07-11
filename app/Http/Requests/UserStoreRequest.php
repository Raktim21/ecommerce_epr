<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserStoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:users,email',
            'phone'   =>   [
                'required',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                'unique:users,phone',
            ],
            'address'          => 'nullable|string',
            'details'          => 'nullable|string',
            'avatar'           => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'password'         => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
            'role_id'          => 'required|exists:roles,id',
            'is_employee'      => 'required|in:0,1',
            'salary'           => 'sometimes|numeric',
            'general_kpi'      => 'sometimes|integer',
            'incentive_kpi'    => 'sometimes|integer',
            'incentive_bonus'  => 'sometimes|integer',
            'document'         => 'sometimes|file|max:2048',
            'joining_date'     => 'sometimes|date_format:Y-m-d'
        ];

        if($this->input('is_employee') == 1)
        {
            $rules['salary']            = 'required';
            $rules['general_kpi']       = 'required';
            $rules['document']          = 'required';
            $rules['joining_date']      = 'required';
        }

        if(!is_null($this->input('incentive_kpi')))
        {
            $rules['incentive_bonus'] = 'required';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'  => false,
            'error'  => $validator->errors()->first(),
        ], 422));
    }
}
