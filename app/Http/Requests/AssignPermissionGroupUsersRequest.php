<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AssignPermissionGroupUsersRequest
 */
class AssignPermissionGroupUsersRequest extends FormRequest
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
            'users' => ['present', 'array'],
            'users.*' => ['string', 'uuid', 'exists:users,uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'users.present' => 'Pole users jest wymagane.',
            'users.array' => 'Pole users musi być tablicą.',
            'users.*.uuid' => 'Identyfikator użytkownika musi być poprawnym UUID.',
            'users.*.exists' => 'Wybrany użytkownik nie istnieje.',
        ];
    }
}
