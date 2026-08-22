<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of CreateManagedRoleRequest
 *
 * Kierownik działu/grupy użytkowników tworzy nową rolę w ramach swojego
 * działu/grupy i od razu nadaje jej uprawnienia (muszą być podzbiorem
 * uprawnień posiadanych przez ten dział/grupę).
 */
class CreateManagedRoleRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,uuid'],
            'permission_groups' => ['nullable', 'array'],
            'permission_groups.*' => ['string', 'exists:permission_groups,uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa roli jest wymagana.',
            'permissions.array' => 'Pole permissions musi być tablicą.',
            'permissions.*.exists' => 'Wybrane uprawnienie nie istnieje.',
            'permission_groups.array' => 'Pole permission_groups musi być tablicą.',
            'permission_groups.*.exists' => 'Wybrana grupa uprawnień nie istnieje.',
        ];
    }
}
