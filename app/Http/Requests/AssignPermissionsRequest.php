<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AssignPermissionsRequest
 *
 * Wspólny request do nadawania uprawnień/grup uprawnień grupom użytkowników,
 * rolom i grupom ról (assignable strona relacji permission_assignments).
 */
class AssignPermissionsRequest extends FormRequest
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
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,uuid'],
            'permission_groups' => ['nullable', 'array'],
            'permission_groups.*' => ['string', 'exists:permission_groups,uuid'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permissions.array' => 'Pole permissions musi być tablicą.',
            'permissions.*.exists' => 'Wybrane uprawnienie nie istnieje.',
            'permission_groups.array' => 'Pole permission_groups musi być tablicą.',
            'permission_groups.*.exists' => 'Wybrana grupa uprawnień nie istnieje.',
            'expires_at.date' => 'Pole expires_at musi być prawidłową datą.',
        ];
    }
}
