<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BillStoreRequest extends FormRequest
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
            'client_id'     => 'required|exists:clients,id',
            'remarks'       => 'nullable|string|max:498',
            'services'      => 'required|array|min:1',
            'services.*'    => ['required',
                                function ($attr, $val, $fail) {
                                    $service = Service::find($val);

                                    if(!$service)
                                    {
                                        $fail('Invalid service selected.');
                                    }

                                    else if ($service->status == 0)
                                    {
                                        $fail($service->name . ' is currently unavailable.');
                                    }
                                }]
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
