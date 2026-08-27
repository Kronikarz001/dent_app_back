<?php

namespace App\Http\Requests;

use App\Rules\PeselRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of UserStoreRequest
 */
class UserStoreRequest extends FormRequest
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
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'pesel' => ['required', 'string', new PeselRule, 'unique:users,pesel'],
            'private_email' => ['required', 'email', 'unique:users,private_email'],
            'pwz_numer' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Imię jest wymagane.',
            'first_name.string' => 'Imię musi być tekstem.',
            'last_name.required' => 'Nazwisko jest wymagane.',
            'last_name.string' => 'Nazwisko musi być tekstem.',
            'email.email' => 'Adres e-mail musi być prawidłowy.',
            'email.unique' => 'Użytkownik o podanym adresie e-mail już istnieje.',
            'pesel.required' => 'PESEL jest wymagany.',
            'pesel.string' => 'PESEL musi być tekstem.',
            'pesel.unique' => 'Użytkownik o podanym numerze PESEL już istnieje.',
            'private_email.required' => 'Prywatny adres e-mail jest wymagany.',
            'private_email.email' => 'Prywatny adres e-mail musi być prawidłowy.',
            'private_email.unique' => 'Użytkownik o podanym prywatnym adresie e-mail już istnieje.',
            'pwz_numer.string' => 'Numer PWZ musi być tekstem.',
        ];
    }
}
