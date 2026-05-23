<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AssignJobPositionRequest
 */
class AssignJobPositionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'job_positions' => ['required', 'array'],
            'job_positions.*' => ['required', 'exists:job_positions,uuid'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
