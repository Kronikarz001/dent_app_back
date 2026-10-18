<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of PatientPhotoStoreRequest
 */
class PatientPhotoStoreRequest extends FormRequest
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
            'files.*' => ['required', 'file', 'max:25600', 'mimes:jpg,jpeg,png,webp'],
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
            'files.*.required' => 'Zdjęcie jest wymagane.',
            'files.*.file' => 'Przesłany element musi być plikiem.',
            'files.*.max' => 'Zdjęcie nie może być większe niż 25 MB.',
            'files.*.mimes' => 'Niedozwolony typ zdjęcia.',
        ];
    }
}
