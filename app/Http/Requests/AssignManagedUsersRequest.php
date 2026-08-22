<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AssignManagedUsersRequest
 *
 * Wspólny request do przypisywania użytkowników do roli/grupy ról z flagą
 * is_manager per użytkownik (kierownik roli/grupy).
 */
class AssignManagedUsersRequest extends FormRequest
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
            'users.*.uuid' => ['required', 'string', 'exists:users,uuid'],
            'users.*.is_manager' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'users.array' => 'Pole users musi być tablicą.',
            'users.*.uuid.required' => 'Każdy wpis musi zawierać uuid użytkownika.',
            'users.*.uuid.exists' => 'Wybrany użytkownik nie istnieje.',
            'users.*.is_manager.boolean' => 'Pole is_manager musi być wartością logiczną.',
        ];
    }
}
