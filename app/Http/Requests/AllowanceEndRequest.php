<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AllowanceEndRequest extends FormRequest
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
            'to_lat'         => 'required',
            'to_lng'         => 'required',
            'visit_type'     => 'required|string|in:Client Hunt,Client Visit,Both',
            'transport_type' => 'required|string|in:Bus,CNG,Uber,Rickshaw,Plane,Train',
            'amount'         => 'required|numeric|gt:20',
            'document'       => 'nullable|file',
            'note'           => 'nullable|string',
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
