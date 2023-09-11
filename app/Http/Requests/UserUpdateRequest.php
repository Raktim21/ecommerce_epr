<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserUpdateRequest extends FormRequest
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
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:users,email,'.$this->route('id'),
            'phone'   =>   [
                'required',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                'string',
                'unique:users,phone,'.$this->route('id'),
            ],

            'address'          => 'nullable|string',
            'details'          => 'nullable|string',
            'avatar'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'role_id'          => 'required|exists:roles,id',
            'salary'           => 'sometimes|numeric',
            'general_kpi'      => 'sometimes|integer',
            'joining_date'     => 'sometimes|date_format:Y-m-d'
        ];
    }

    function messages()
    {

        return [
            'name.required'    => 'Please provide a name',
            'name.string'      => 'Invalid name',
            'name.max'         => 'Name is too long',
            'email.required'   => 'Please provide an email',
            'email.string'     => 'Invalid email',
            'email.max'        => 'Email is too long',
            'email.email'      => 'Invalid email',
            'phone.required'   => 'Please provide a phone number',
            'phone.string'     => 'Invalid phone number',
            'phone.max'        => 'Phone number is too long',
            'phone.regex'      => 'Invalid phone number',
            'address.string'   => 'Invalid address',
            'details.string'   => 'Invalid details',
            'avatar.image'     => 'Invalid avatar',
            'avatar.mimes'     => 'Invalid avatar',
            'avatar.max'       => 'Avatar is too large',
            'role_id.required' => 'Please provide a role',
            'role_id.exists'   => 'Invalid role',
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
