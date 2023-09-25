<?php

namespace App\Imports;

use App\Models\Clients;
use App\Models\ClientTransaction;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClientTransactionsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new ClientTransaction([
            'client_id'         => $row['client_id'],
            'invoice_no'        => 'TRX-'.rand(100,999).'-'.time(),
            'payment_type_id'   => $row['payment_type_id'],
            'transaction_id'    => $row['transaction_id'],
            'amount'            => $row['amount'],
            'occurred_on'       => $row['occurred_on'],
            'remarks'           => $row['remarks']
        ]);
    }

    public function rules(): array
    {
        return [
            '*.client_id'         => ['required',
                function ($attr, $val, $fail) {
                    $client = Clients::where('id', $val)
                        ->whereNotNull('confirmation_date')->first();

                    if(!$client)
                    {
                        $fail('Invalid client ID: ' . $val);
                    }
                }],
            '*.payment_type_id'   => 'required|exists:payment_types,id',
            '*.transaction_id'    => 'required|unique:client_transactions,transaction_id',
            '*.amount'            => 'required|numeric|gt:0',
            '*.occurred_on'       => 'required|date_format:Y-m-d H:i|before_or_equal:'.date('Y-m-d H:i'),
            '*.remarks'           => 'nullable|string|max:498'
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.payment_type_id.exists'  => 'Invalid payment type ID',
            '*.transaction_id.unique'   => 'One of the transaction IDs already exists',
            '*.amount'                  => 'Amounts must be greater than 0'
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
