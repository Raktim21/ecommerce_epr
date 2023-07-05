<?php

namespace App\Http\Requests;

use App\Models\TransportAllowance;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AllowanceStartRequest extends FormRequest
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
            'from_lat'       => 'required',
            'from_lng'       => 'required',
            'visit_type'     => 'required|string|in:Client Hunt, Client Re-visit, Both',
            'transport_type' => 'nullable|string|in:Bus,CNG,Uber,Rickshaw,Plane,Train',
            'amount'         => 'nullable|numeric',
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
