<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of PatientPhotoUpdateRequest
 */
class PatientPhotoUpdateRequest extends FormRequest
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
            'filename.required' => 'Nazwa zdjęcia jest wymagana.',
            'filename.string' => 'Nazwa zdjęcia musi być tekstem.',
            'filename.max' => 'Nazwa zdjęcia może mieć maksymalnie 255 znaków.',
        ];
    }
}
