<?php

namespace App\Http\Requests;

use App\Models\Clients;
use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PaymentStoreRequest extends FormRequest
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
            'client_id'             => ['required','exists:clients,id',
                                            function ($attr, $val, $fail)
                                            {
                                                $client = Clients::find($val);

                                                if(is_null($client))
                                                {
                                                    $fail('Invalid client selected.');
                                                }

                                                else if($client->company=='N/A' || $client->name=='N/A' || $client->email=='N/A' ||
                                                    $client->phone_no=='N/A' || $client->area=='N/A' ||
                                                    $client->product_type=='N/A' || $client->document==null)
                                                {
                                                    $fail("Insufficient client information.");
                                                }

                                                else if($client->interest_status != 100) {
                                                    $fail("The selected client must have an interest rate of 100.");
                                                }
                                            }],
            'payment_type_id'       => 'required|exists:payment_types,id',
            'service_id'            => 'required|exists:services,id',
            'transaction_id'        => 'required_if:payment_type_id,2|string|max:48',
            'website_domain'        => 'required|url|unique:websites,domain|max:98'
        ];
    }

    public function messages()
    {
        return [
            'payment_type_id.required' => 'Please select a payment type.',
            'service_id.required' => 'Please select a service.',
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
