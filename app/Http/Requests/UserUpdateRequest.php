<?php

namespace App\Http\Requests;

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
            'pesel' => ['nullable', 'string', 'size:11', Rule::unique('users', 'pesel')->ignore($this->route('user'))],
            'private_email' => ['nullable', 'email', Rule::unique('users', 'private_email')->ignore($this->route('user'))],
            'private_phone_number' => ['nullable', 'string', new PhoneNumberRule],
            'phone_number' => ['nullable', 'string', new PhoneNumberRule],
            'is_active' => ['nullable', 'boolean'],
            'job_position_uuid' => ['nullable', 'string', 'exists:job_positions,uuid'],
            'street' => ['nullable', 'string'],
            'house_number' => ['nullable', 'string'],
            'apartment_number' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
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
            'pesel.size' => 'PESEL musi mieć dokładnie 11 znaków.',
            'pesel.unique' => 'Użytkownik o podanym numerze PESEL już istnieje.',
            'private_email.email' => 'Prywatny adres e-mail musi być prawidłowy.',
            'private_email.unique' => 'Użytkownik o podanym prywatnym adresie e-mail już istnieje.',
            'is_active.boolean' => 'Pole is_active musi być wartością logiczną.',
            'job_position_uuid.exists' => 'Wybrane stanowisko nie istnieje.',
            'street.string' => 'Pole street musi być tekstem.',
            'house_number.string' => 'Pole house_number musi być tekstem.',
            'apartment_number.string' => 'Pole apartment_number musi być tekstem.',
            'postal_code.string' => 'Pole postal_code musi być tekstem.',
            'city.string' => 'Pole city musi być tekstem.',
        ];
    }
}
