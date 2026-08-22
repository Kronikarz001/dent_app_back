<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of JobPositionRequest
 */
class JobPositionRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'f_name' => ['required', 'string'],
            'm_name' => ['required', 'string'],
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
            'name.required' => 'Nazwa jest wymagana.',
            'name.string' => 'Nazwa musi być tekstem.',
            'f_name.required' => 'Nazwa w formie żeńskiej jest wymagana.',
            'f_name.string' => 'Nazwa w formie żeńskiej musi być tekstem.',
            'm_name.required' => 'Nazwa w formie męskiej jest wymagana.',
            'm_name.string' => 'Nazwa w formie męskiej musi być tekstem.',
        ];
    }
}
