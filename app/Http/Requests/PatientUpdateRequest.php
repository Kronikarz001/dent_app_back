<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\PhoneNumberType;
use App\Rules\PhoneNumberRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Summary of PatientUpdateRequest
 */
class PatientUpdateRequest extends FormRequest
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
            'email' => ['required', 'email', Rule::unique('patients', 'email')->ignore($this->route('patient'))],
            'phone_numbers' => ['nullable', 'array'],
            'phone_numbers.*.number' => ['required', 'string', new PhoneNumberRule],
            'phone_numbers.*.type' => ['required', 'string', new Enum(PhoneNumberType::class)],
            'street' => ['nullable', 'string'],
            'house_number' => ['nullable', 'string'],
            'apartment_number' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'gender' => ['nullable', new Enum(Gender::class)],
            'notes' => ['nullable', 'string'],
            'doctor_uuid' => ['nullable', 'string', 'exists:users,uuid'],
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
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.email' => 'Adres e-mail musi być prawidłowy.',
            'email.unique' => 'Pacjent o podanym adresie e-mail już istnieje.',
            'phone_numbers.array' => 'Pole phone_numbers musi być tablicą.',
            'phone_numbers.*.number.required' => 'Numer telefonu jest wymagany.',
            'phone_numbers.*.type.required' => 'Typ numeru telefonu jest wymagany.',
            'phone_numbers.*.type.enum' => 'Wybrany typ numeru telefonu jest nieprawidłowy.',
            'street.string' => 'Pole street musi być tekstem.',
            'house_number.string' => 'Pole house_number musi być tekstem.',
            'apartment_number.string' => 'Pole apartment_number musi być tekstem.',
            'postal_code.string' => 'Pole postal_code musi być tekstem.',
            'city.string' => 'Pole city musi być tekstem.',
            'gender.enum' => 'Wybrana płeć jest nieprawidłowa.',
            'notes.string' => 'Pole notes musi być tekstem.',
            'doctor_uuid.exists' => 'Wybrany lekarz nie istnieje.',
        ];
    }
}
