<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class AllowanceFilterRequest extends FormRequest
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
            'start_date'         => 'sometimes|required|date|date_format:Y-m-d|before:end_date|before:today',
            'end_date'           => 'sometimes|required|date|date_format:Y-m-d|after:start_date',
            'search'             => 'sometimes|string',
            'amount_start_range' => 'sometimes|numeric|gte:0',
            'amount_end_range'   => 'sometimes|numeric',
        ];

        if($this->input('end_date'))
        {
            $rules['start_date'] = 'required';
        }

        if($this->input('amount_end_range'))
        {
            $rules['amount_start_range'] = 'required|gte:'.$this->input('amount_start_range');
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
