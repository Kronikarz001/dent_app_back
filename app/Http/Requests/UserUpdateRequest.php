<?php

namespace App\Http\Requests;

use App\Rules\PeselRule;
use App\Rules\PhoneNumberRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Summary of UserUpdateRequest
 */
class UserUpdateRequest extends FormRequest
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
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', 'confirmed'],
            'pesel' => ['nullable', 'string', new PeselRule, Rule::unique('users', 'pesel')->ignore($this->route('user'))],
            'private_email' => ['nullable', 'email', Rule::unique('users', 'private_email')->ignore($this->route('user'))],
            'private_phone_number' => ['nullable', 'string', new PhoneNumberRule],
            'phone_number' => ['nullable', 'string', new PhoneNumberRule],
            'is_active' => ['nullable', 'boolean'],
            'pwz_numer' => ['nullable', 'string'],
            'job_positions' => ['nullable', 'array'],
            'job_positions.*' => ['string', 'exists:job_positions,uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.string' => 'Imię musi być tekstem.',
            'last_name.string' => 'Nazwisko musi być tekstem.',
            'email.email' => 'Adres e-mail musi być prawidłowy.',
            'email.unique' => 'Użytkownik o podanym adresie e-mail już istnieje.',
            'password.confirmed' => 'Potwierdzenie hasła nie jest zgodne.',
            'pesel.unique' => 'Użytkownik o podanym numerze PESEL już istnieje.',
            'private_email.email' => 'Prywatny adres e-mail musi być prawidłowy.',
            'private_email.unique' => 'Użytkownik o podanym prywatnym adresie e-mail już istnieje.',
            'is_active.boolean' => 'Pole is_active musi być wartością logiczną.',
            'pwz_numer.string' => 'Numer PWZ musi być tekstem.',
            'job_positions.array' => 'Pole job_positions musi być tablicą.',
            'job_positions.*.exists' => 'Wybrane stanowisko nie istnieje.',
        ];
    }
}
