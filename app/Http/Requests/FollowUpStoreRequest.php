<?php

namespace App\Http\Requests;

use App\Models\Clients;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FollowUpStoreRequest extends FormRequest
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
            'client_id' => ['required',
                            function($attr, $val, $fail) {
                                $client = Clients::where('id', $val)
                                    ->whereNull('confirmation_date')
                                    ->first();

                                if(!$client)
                                {
                                    $fail('Invalid client.');
                                }
                            }],
            'detail' => 'required|string',
            'occurred_on' => 'required|date_format:Y-m-d H:i:s',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
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
