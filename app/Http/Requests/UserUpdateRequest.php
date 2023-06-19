<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
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
            'role_id'          => 'required|exists:roles,id'
        ];
    }
}
