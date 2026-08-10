<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of ResetPasswordRequest
 */
class ResetPasswordRequest extends FormRequest
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
     * @return array<string,ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Token jest wymagany.',
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.email' => 'Adres e-mail musi być prawidłowy.',
            'email.exists' => 'Nie znaleziono użytkownika o podanym adresie e-mail.',
            'password.required' => 'Hasło jest wymagane.',
            'password.min' => 'Hasło musi mieć co najmniej 8 znaków.',
            'password.confirmed' => 'Potwierdzenie hasła nie jest zgodne.',
        ];
    }
}
