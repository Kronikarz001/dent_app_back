<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AssignPermissionGroupPermissionsRequest
 */
class AssignPermissionGroupPermissionsRequest extends FormRequest
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
        ];
    }
}
