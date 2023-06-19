<?php

namespace App\Http\Requests;

use App\Models\Clients;
use App\Models\Payment;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $transaction_id = $this->input('transaction_id');

        $rules = [
            'client_id' => ['required','unique:payments,client_id',
                function ($attr, $val, $fail)
                {
                    $client = Clients::find($val);

                    if(!$client || $client->company=='N/A' || $client->name=='N/A' || $client->email=='N/A' ||
                        $client->phone_no=='N/A' || $client->area=='N/A' ||
                        $client->product_type=='N/A' || $client->document==null)
                    {
                        $fail("Insufficient client information.");
                    }

                    if(!$client || $client->interest_status != 100) {
                        $fail("The selected client must have an interest rate of 100.");
                    }
                }],
            'payment_type_id' => 'required|exists:payment_types,id',
            'transaction_id' => 'nullable|string',
            'amount' => 'required|numeric|in:999',
        ];

        if($this->input('payment_type_id') == 2)
        {
            $rules['transaction_id'] = 'required';
        }
        if(!is_null($transaction_id))
        {
            $rules['transaction_id'] = [
                Rule::notIn(Payment::pluck('transaction_id')->toArray())
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'client_id.unique' => 'The selected client has already paid.',
            'amount.in' => 'The amount must be equal to 999.',
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
