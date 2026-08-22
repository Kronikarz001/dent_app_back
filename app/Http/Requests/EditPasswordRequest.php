<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of EditPasswordRequest
 */
class EditPasswordRequest extends FormRequest
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
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Obecne hasło jest wymagane.',
            'current_password.current_password' => 'Podane hasło jest nieprawidłowe.',
            'password.required' => 'Nowe hasło jest wymagane.',
            'password.string' => 'Nowe hasło musi być tekstem.',
            'password.min' => 'Nowe hasło musi mieć co najmniej 8 znaków.',
            'password.confirmed' => 'Potwierdzenie hasła nie jest zgodne.',
        ];
    }
}
