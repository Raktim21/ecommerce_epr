<?php

namespace App\Http\Requests;

use App\Models\FollowUpReminder;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FollowUpReminderRequest extends FormRequest
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
            'follow_up_session'     => 'required|date_format:Y-m-d H:i|after:'.date('Y-m-d'),
            'client_id'             => ['required', 'integer',
                                        function ($attr, $val, $fail) {
                                            if(FollowUpReminder::where('client_id', $val)
                                                ->where('followup_session', Carbon::parse($this->input('follow_up_session'))->format('Y-m-d H:i:s'))
                                                ->exists()
                                            ) {
                                                $fail('The client already has a follow up session at the selected time');
                                            }
                                        }],
            'notes'                 => 'nullable|string|max:500'
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
