<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of LoginRequest
 *
 * @property string $email
 * @property string $password
 */
class LoginRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.string' => 'Adres e-mail musi być tekstem.',
            'email.email' => 'Adres e-mail musi być prawidłowy.',
            'password.required' => 'Hasło jest wymagane.',
            'password.string' => 'Hasło musi być tekstem.',
            'password.min' => 'Hasło musi mieć co najmniej 8 znaków.',
        ];
    }
}
