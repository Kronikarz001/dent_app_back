<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AssignEmployeeScheduleUsersRequest
 */
class AssignEmployeeScheduleUsersRequest extends FormRequest
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
            'users' => ['nullable', 'array'],
            'users.*' => ['string', 'exists:users,uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'users.array' => 'Pole users musi być tablicą.',
            'users.*.exists' => 'Wybrany użytkownik nie istnieje.',
        ];
    }
}
