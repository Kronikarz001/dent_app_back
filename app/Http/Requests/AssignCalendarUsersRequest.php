<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AssignCalendarUsersRequest
 */
class AssignCalendarUsersRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'users' => ['nullable', 'array'],
            'users.*' => ['string', 'exists:users,uuid'],
            'patients' => ['nullable', 'array'],
            'patients.*' => ['string', 'exists:patients,uuid'],
        ];
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'users.array' => 'Pole users musi być tablicą.',
            'users.*.exists' => 'Wybrany użytkownik nie istnieje.',
            'patients.array' => 'Pole patients musi być tablicą.',
            'patients.*.exists' => 'Wybrany pacjent nie istnieje.',
        ];
    }
}
