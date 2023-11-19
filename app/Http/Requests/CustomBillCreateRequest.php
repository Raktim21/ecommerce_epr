<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomBillCreateRequest extends FormRequest
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
            'client_name'       => 'required|string|max:100',
            'client_email'      => 'required|email|max:100',
            'client_phone'      => ['required','regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/'],
            'client_company'    => 'required|string|max:100',
            'items'             => 'required|array',
            'items.*.item'      => 'required|string|max:255|distinct',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.amount'    => 'required|numeric'
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
