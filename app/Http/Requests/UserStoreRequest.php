<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:148',
            'email'   => 'required|string|email|max:148|unique:users,email',
            'phone'   =>   [
                'required',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                'unique:users,phone',
            ],
            'address'          => 'nullable|string|max:498',
            'details'          => 'nullable|string|max:498',
            'avatar'           => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'password'         => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
            'role_id'          => 'required|exists:roles,id',
            'is_employee'      => 'required|in:0,1',
            'salary'           => 'required_if:is_employee,1|numeric',
            'document'         => 'required_if:is_employee,1|file|max:2048',
            'joining_date'     => 'required_if:is_employee,1|date_format:Y-m-d'
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
