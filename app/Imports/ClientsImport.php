<?php

namespace App\Imports;

use App\Models\Clients;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
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
            'email' => $row['email'],
            'phone_no' => $row['phone_no'],
            'area' => $row['area'],
            'status_id' => 1,
            'product_type' => $row['product_type'],
            'client_opinion' => $row['client_opinion'] ?? '',
            'officer_opinion' => $row['officer_opinion'] ?? '',
            'added_by' => auth()->user()->id,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.company'          => 'required|string|max:255',
            '*.name'             => 'required|string|max:255',
            '*.email'            => 'required|email|unique:clients,email',
            '*.phone_no'         =>   [
                'required',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
                'unique:clients,phone_no',
            ],
            '*.area'             => 'required|string',
            '*.product_type'     => 'required|string|max:255',
            '*.client_opinion'   => 'nullable|string',
            '*.officer_opinion'  => 'nullable|string',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.company.required'      => 'The company field is required.',
            '*.name.required'         => 'The name field is required.',
            '*.email.required'        => 'The email field is required.',
            '*.email.email'           => 'The email field must have a valid email address.',
            '*.email.unique'          => 'The selected email already exists.',
            '*.phone_no.required'     => 'The phone no field is required.',
            '*.phone_no.regex'        => 'The phone no field must have a valid number.',
            '*.phone_no.unique'       => 'The selected phone no already exists.',
            '*.area.required'         => 'The area field is required.',
            '*.product_type.required' => 'The product type field is required.',
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
