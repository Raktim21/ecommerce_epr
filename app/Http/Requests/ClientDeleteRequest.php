<?php

namespace App\Http\Requests;

use App\Models\Clients;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientDeleteRequest extends FormRequest
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
            'ids' => ['required','array',
                function($attr, $val, $fail)
                {
                    $clients = is_array($val) ? $val : [$val];

                    $isNotConfirmed = Clients::whereIn('id',$clients)
                        ->where(function ($query) {
                            $query->whereNull('confirmation_date');
                        })->count();

                    if($isNotConfirmed != count($clients))
                    {
                        $fail('Confirmed clients can not be deleted.');
                    }
                }
            ]
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
