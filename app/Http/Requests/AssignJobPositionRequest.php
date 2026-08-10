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
            'job_positions' => ['required', 'array'],
            'job_positions.*' => ['required', 'exists:job_positions,uuid'],
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
            'job_positions.required' => 'Pole job_positions jest wymagane.',
            'job_positions.array' => 'Pole job_positions musi być tablicą.',
            'job_positions.*.required' => 'Identyfikator stanowiska jest wymagany.',
            'job_positions.*.exists' => 'Wybrane stanowisko nie istnieje.',
        ];
    }
}
