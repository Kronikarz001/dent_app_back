<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumberRule;
use App\Rules\ZipCodeRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'email' => ['nullable', 'email'],
            'password' => ['nullable', 'string', 'confirmed'],
            'pesel' => ['nullable', 'string', 'size:11'],
            'private_email' => ['nullable', 'email'],
            'private_phone_number' => ['nullable', 'string', new PhoneNumberRule],
            'phone_number' => ['nullable', 'string', new PhoneNumberRule],
            'is_active' => ['nullable', 'boolean'],
            'job_position_uuid' => ['nullable', 'string', 'exists:job_positions,uuid'],
            'street' => ['nullable', 'string'],
            'house_number' => ['nullable', 'string'],
            'apartment_number' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string', new ZipCodeRule],
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
            'password.confirmed' => 'Potwierdzenie hasła nie jest zgodne.',
            'pesel.size' => 'PESEL musi mieć dokładnie 11 znaków.',
            'private_email.email' => 'Prywatny adres e-mail musi być prawidłowy.',
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
