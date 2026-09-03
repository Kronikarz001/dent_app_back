<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Rules\PeselRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Summary of PatientStoreRequest
 */
class PatientStoreRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:patients,email'],
            'pesel' => ['required', 'string', new PeselRule, 'unique:patients,pesel'],
            'street' => ['nullable', 'string'],
            'house_number' => ['nullable', 'string'],
            'apartment_number' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'gender' => ['nullable', new Enum(Gender::class)],
            'notes' => ['nullable', 'string'],
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
            'pesel.required' => 'PESEL jest wymagany.',
            'pesel.string' => 'PESEL musi być tekstem.',
            'pesel.unique' => 'Pacjent o podanym numerze PESEL już istnieje.',
            'street.string' => 'Pole street musi być tekstem.',
            'house_number.string' => 'Pole house_number musi być tekstem.',
            'apartment_number.string' => 'Pole apartment_number musi być tekstem.',
            'postal_code.string' => 'Pole postal_code musi być tekstem.',
            'city.string' => 'Pole city musi być tekstem.',
            'gender.enum' => 'Wybrana płeć jest nieprawidłowa.',
            'notes.string' => 'Pole notes musi być tekstem.',
        ];
    }
}
