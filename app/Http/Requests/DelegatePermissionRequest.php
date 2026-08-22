<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of DelegatePermissionRequest
 *
 * Kierownik roli/grupy ról oddaje jedno ze swoich aktualnie posiadanych
 * uprawnień (albo grupę uprawnień) innej osobie z tej samej roli/grupy.
 */
class DelegatePermissionRequest extends FormRequest
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
            'user_uuid' => ['required', 'string', 'exists:users,uuid'],
            'permission_uuid' => ['nullable', 'required_without:permission_group_uuid', 'prohibits:permission_group_uuid', 'string', 'exists:permissions,uuid'],
            'permission_group_uuid' => ['nullable', 'required_without:permission_uuid', 'string', 'exists:permission_groups,uuid'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_uuid.required' => 'Wybrany użytkownik jest wymagany.',
            'user_uuid.exists' => 'Wybrany użytkownik nie istnieje.',
            'permission_uuid.required_without' => 'Podaj permission_uuid albo permission_group_uuid.',
            'permission_uuid.prohibits' => 'Podaj tylko jedno: permission_uuid albo permission_group_uuid.',
            'permission_uuid.exists' => 'Wybrane uprawnienie nie istnieje.',
            'permission_group_uuid.required_without' => 'Podaj permission_uuid albo permission_group_uuid.',
            'permission_group_uuid.exists' => 'Wybrana grupa uprawnień nie istnieje.',
            'expires_at.date' => 'Pole expires_at musi być prawidłową datą.',
        ];
    }
}
