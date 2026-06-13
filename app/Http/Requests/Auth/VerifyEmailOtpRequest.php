<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailOtpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $length = (int) config('verification.code_length', 6);

        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:'.$length, 'regex:/^\d+$/'],
        ];
    }

    public function messages()
    {
        $length = (int) config('verification.code_length', 6);

        return [
            'code.size' => __('Please enter the :length-digit verification code.', ['length' => $length]),
            'code.regex' => __('Verification code must contain only numbers.'),
        ];
    }
}
