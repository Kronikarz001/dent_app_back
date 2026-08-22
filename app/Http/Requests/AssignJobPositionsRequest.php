<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AssignJobPositionsRequest
 *
 * Wspólny request do przypisywania stanowisk do działu/grupy użytkowników,
 * zastępując poprzednią listę.
 */
class AssignJobPositionsRequest extends FormRequest
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
            'job_positions' => ['nullable', 'array'],
            'job_positions.*' => ['string', 'exists:job_positions,uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'job_positions.array' => 'Pole job_positions musi być tablicą.',
            'job_positions.*.exists' => 'Wybrane stanowisko nie istnieje.',
        ];
    }
}
