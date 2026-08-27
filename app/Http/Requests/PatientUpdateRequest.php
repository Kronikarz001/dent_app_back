<?php

namespace App\Http\Requests;

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
        ];
    }
}
