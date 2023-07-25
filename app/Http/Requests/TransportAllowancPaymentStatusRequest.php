<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class TransportAllowancPaymentStatusRequest extends FormRequest
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
            
            'payment_status' => 'required|in:0,1',
            'transport_allowance_id'   => 'required|array',
            'transport_allowance_id.*' => 'required|exists:transport_allowances,id',
            
        ];
    }



    public function messages()
    {
        return [
            'payment_status.required'  => 'Please select a payment status.',
            'payment_status.in'        => 'Please select a valid payment status.',
            'transport_allowance_id.required'    => 'Please select an allowance.',
            'transport_allowance_id.array'       => 'Please select a valid allowance.',
            'transport_allowance_id.*.required'  => 'Please select an allowance.',
            'transport_allowance_id.*.exists'    => 'Please select a valid allowance.',
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
