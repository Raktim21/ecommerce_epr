<?php

namespace App\Http\Requests;

use App\Models\Clients;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientTransactionRequest extends FormRequest
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
            'client_id'         => ['required',
                                    function ($attr, $val, $fail) {
                                        $client = Clients::where('id', $val)
                                            ->whereNotNull('confirmation_date')->first();

                                        if(!$client)
                                        {
                                            $fail('Invalid client.');
                                        }
                                    }],
            'payment_type_id'   => 'required|exists:payment_types,id',
            'transaction_id'    => 'required|unique:client_transactions,transaction_id',
            'amount'            => 'required|numeric|gt:0',
            'occurred_on'       => 'required|date_format:Y-m-d H:i|before_or_equal:'.date('Y-m-d H:i'),
            'remarks'           => 'nullable|string|max:498'
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
