<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of MessageGroupUpdateRequest
 */
class MessageGroupUpdateRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
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
            'name.required' => 'Nazwa grupy jest wymagana.',
            'name.string' => 'Nazwa grupy musi być tekstem.',
            'name.max' => 'Nazwa grupy może mieć maksymalnie 255 znaków.',
        ];
    }
}
