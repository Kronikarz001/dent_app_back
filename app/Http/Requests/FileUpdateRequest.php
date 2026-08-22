<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of FileUpdateRequest
 */
class FileUpdateRequest extends FormRequest
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
            'filename' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'filename.required' => 'Nazwa pliku jest wymagana.',
            'filename.string' => 'Nazwa pliku musi być tekstem.',
            'filename.max' => 'Nazwa pliku może mieć maksymalnie 255 znaków.',
        ];
    }
}
