<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of FileStoreRequest
 */
class FileStoreRequest extends FormRequest
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
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'files.required' => 'Pole files jest wymagane.',
            'files.array' => 'Pole files musi być tablicą.',
            'files.*.required' => 'Plik jest wymagany.',
            'files.*.file' => 'Przesłany element musi być plikiem.',
        ];
    }
}
