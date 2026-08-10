<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of MaterialRequest
 */
class MaterialRequest extends FormRequest
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
            'dental_examinations' => ['nullable', 'array'],
            'dental_examinations.*' => ['string', 'exists:dental_examinations,uuid'],
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
            'dental_examinations.array' => 'Pole dental_examinations musi być tablicą.',
            'dental_examinations.*.exists' => 'Wybrane badanie stomatologiczne nie istnieje.',
        ];
    }
}
