<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'pesel' => ['required', 'string', 'size:11', 'unique:patients,pesel'],
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
            'pesel.size' => 'PESEL musi mieć dokładnie 11 znaków.',
            'pesel.unique' => 'Pacjent o podanym numerze PESEL już istnieje.',
        ];
    }
}
