<?php

namespace App\Http\Requests;

use App\Models\Clients;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        return [
            'client_id' => ['required','unique:payments,client_id',
                function ($attr, $val, $fail)
                {
                    $client = Clients::find($val);

                    if(!$client || $client->company=='N/A' || $client->name=='N/A' || $client->email=='N/A' ||
                        $client->phone_no=='N/A' || $client->email=='N/A' || $client->area=='N/A' ||
                        $client->product_type=='N/A' || $client->document==null)
                    {
                        $fail("Insufficient client information.");
                    }

                    if(!$client || $client->status_id != 11) {
                        $fail("The selected client must have an interest rate of 100.");
                    }
                }],
            'amount' => 'required|numeric',
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
