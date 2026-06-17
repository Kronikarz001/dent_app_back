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
}
