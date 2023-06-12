<?php

namespace App\Imports;

use App\Models\Clients;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClientsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }

    public function model(array $row)
    {
        return new Clients([
            'company' => $row['company'],
            'name' => $row['name'],
            'email' => $row['email'] ?? 'N/A',
            'phone_no' => '0' . $row['phone_no'],
            'area' => $row['area'] ?? 'N/A',
            'status_id' => 1,
            'product_type' => $row['product_type'] ?? 'N/A',
            'client_opinion' => $row['client_opinion'] ?? 'N/A',
            'officer_opinion' => $row['officer_opinion'] ?? 'N/A',
            'added_by' => auth()->user()->id,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.company'          => 'required|string|max:255',
            '*.name'             => 'required|string|max:255',
            '*.email'            => 'sometimes|nullable|email',
            '*.phone_no'         =>   [
                'required',
                'regex:/^1[3-9]\d{8}$/',
                'unique:clients,phone_no',
            ],
            '*.area'             => 'sometimes|nullable|string|max:255',
            '*.product_type'     => 'sometimes|nullable|string|max:255',
            '*.client_opinion'   => 'sometimes|nullable|string',
            '*.officer_opinion'  => 'sometimes|nullable|string',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.company.required'      => 'The company field is required.',
            '*.name.required'         => 'The name field is required.',
            '*.email.email'           => 'The email field must have a valid email address.',
            '*.phone_no.required'     => 'The phone no field is required.',
            '*.phone_no.regex'        => 'The phone no field must have a valid number.',
            '*.phone_no.unique'       => 'The selected phone no already exists.',
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
