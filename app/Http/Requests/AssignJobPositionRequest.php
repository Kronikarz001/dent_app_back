<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AssignJobPositionRequest
 */
class AssignJobPositionRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'job_position_uuid' => ['required', 'string', 'exists:job_positions,uuid'],
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
            'job_position_uuid.required' => 'Pole job_position_uuid jest wymagane.',
            'job_position_uuid.string' => 'Pole job_position_uuid musi być tekstem.',
            'job_position_uuid.exists' => 'Wybrane stanowisko nie istnieje.',
        ];
    }
}
