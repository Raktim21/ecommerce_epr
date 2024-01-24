<?php

namespace App\Http\Requests;

use App\Models\Payment;
use App\Models\Website;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientUpdateInfoRequest extends FormRequest
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
            'email'            => 'required|string|email|max:255',
            'phone_no'         =>   [
                'required',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                'unique:clients,phone_no,'.$this->route('id'),
            ],
            'interest_status'  => 'required|integer|lte:100',
            'product_type'     => 'required|string|max:255',
            'area'             => 'required|string',
            'client_opinion'   => 'nullable|string',
            'officer_opinion'  => 'nullable|string',
            'domain'           => ['sometimes','string','url',
                                    function($attr, $val, $fail) {
                                        $domain = Website::where('domain', $val)->first();

                                        if ($domain && $domain->client_id != $this->route('id'))
                                        {
                                            $fail('The domain is already taken.');
                                        }
                                    }],
            'amount'           => 'required_with:payment_type_id|numeric|gt:0',
            'payment_type_id'  => 'required_with:amount|exists:payment_types,id',
            'transaction_id'   => ['sometimes','string','max:50',
                                    function($attr, $val, $fail) {
                                        $payment = Payment::where('transaction_id', $val)->first();

                                        if ($payment && $payment->client_id != $this->route('id'))
                                        {
                                            $fail('Transaction ID is already taken.');
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
