<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FoodAllowanceStoreRequest extends FormRequest
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
            'lat'            => 'required',
            'lng'            => 'required',
            'amount'         => 'required|numeric',
            'note'           => 'nullable|string',
            'document'       => 'nullable|file',
            'client_id'      => 'nullable|exists:clients,id',
            'follow_up_id'   => 'nullable|exists:follow_up_infos,id'
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
