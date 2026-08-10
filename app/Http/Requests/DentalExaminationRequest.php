<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of DentalExaminationRequest
 */
class DentalExaminationRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'price' => ['nullable', 'integer'],
            'materials' => ['nullable', 'array'],
            'materials.*' => ['string', 'exists:materials,uuid'],
            'calendars' => ['nullable', 'array'],
            'calendars.*' => ['string', 'exists:calendars,uuid'],
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
            'description.string' => 'Opis musi być tekstem.',
            'short_description.string' => 'Krótki opis musi być tekstem.',
            'price.integer' => 'Cena musi być liczbą całkowitą.',
            'materials.array' => 'Pole materials musi być tablicą.',
            'materials.*.exists' => 'Wybrany materiał nie istnieje.',
            'calendars.array' => 'Pole calendars musi być tablicą.',
            'calendars.*.exists' => 'Wybrany kalendarz nie istnieje.',
        ];
    }
}
