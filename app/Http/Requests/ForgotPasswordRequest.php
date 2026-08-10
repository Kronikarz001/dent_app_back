<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of ForgotPasswordRequest
 */
class ForgotPasswordRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.email' => 'Adres e-mail musi być prawidłowy.',
            'email.exists' => 'Nie znaleziono użytkownika o podanym adresie e-mail.',
        ];
    }
}
