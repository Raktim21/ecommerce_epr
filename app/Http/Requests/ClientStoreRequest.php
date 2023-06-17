<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientStoreRequest extends FormRequest
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
            'company'          => 'required|string|max:255',
            'name'             => 'required|string|max:255',
            'email'            => 'nullable|email|unique:clients,email',
            'phone_no'         =>   [
                'required',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                'unique:clients,phone_no',
            ],
            'area'             => 'required|string',
            'product_type'     => 'required|string|max:255',
            'client_opinion'   => 'nullable|string',
            'officer_opinion'  => 'nullable|string',
            'document'         => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
            'latitude'         => 'required|string',
            'longitude'        => 'required|string',
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
