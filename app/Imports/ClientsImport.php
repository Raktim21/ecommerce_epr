<?php

namespace App\Imports;

use App\Models\Clients;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClientsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if($row['company'] != null || $row['name'] != null || $row['phone_no'] != null || $row['area'] != null)
        {
            return new Clients([
                'company' => $row['company'] ?? 'N/A',
                'name' => $row['name'] ?? 'N/A',
//                'email' => $row['email'] ?? 'N/A',
                'phone_no' => $row['phone_no'] ?? 'N/A',
                'area' => $row['area'] ?? 'N/A',
                'status_id' => 1,
                'product_type' => $row['product_type'] ?? 'N/A',
                'client_opinion' => $row['client_opinion'] ?? 'N/A',
                'officer_opinion' => $row['officer_opinion'] ?? 'N/A',
                'added_by' => auth()->user()->id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
//            '*.company'          => 'required|string|max:255',
//            '*.name'             => 'required|string|max:255',
            '*.email'            => 'sometimes|nullable|email',
//            '*.phone_no'         =>   [
//                'required',
//                'regex:/^1[3-9]\d{8}$/',
//                'unique:clients,phone_no',
//            ],
//            '*.area'             => 'sometimes|nullable|string|max:255',
//            '*.product_type'     => 'sometimes|nullable|string|max:255',
//            '*.client_opinion'   => 'sometimes|nullable|string',
//            '*.officer_opinion'  => 'sometimes|nullable|string',
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
