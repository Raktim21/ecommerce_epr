<?php

namespace App\Http\Requests;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TodoStoreRequest extends FormRequest
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
            'title'             => ['required','string','max:250',
                                    function($attr, $val, $fail) {
                                        $exists = Todo::where('title', $val)
                                            ->where('added_by', auth()->user()->id)->first();

                                        if($exists)
                                        {
                                            $fail('You already have a task with same title.');
                                        }
                                    }],
            'detail'            => 'nullable|string|max:498',
            'priority_level'    => 'required|in:1,2,3',
            'users'             => 'required|array|min:1',
            'users.*'           => ['required',
                                    function ($attr, $val, $fail) {
                                        $user = User::find($val);

                                        if(!$user || $user->is_active == 0)
                                        {
                                            $fail('Invalid user selected.');
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
