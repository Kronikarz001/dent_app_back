<?php

namespace App\Http\Requests;

use App\Enums\ReimbursementLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Summary of MedicineStoreRequest
 */
class MedicineStoreRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'ean_gtin' => ['nullable', 'string', 'max:14', 'unique:medicines,ean_gtin'],
            'reimbursement_level' => ['required', new Enum(ReimbursementLevel::class)],
            'external_reference' => ['nullable', 'string'],
        ];
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
            'ean_gtin.string' => 'Pole ean_gtin musi być tekstem.',
            'ean_gtin.unique' => 'Lek o podanym kodzie EAN/GTIN już istnieje.',
            'reimbursement_level.required' => 'Poziom odpłatności jest wymagany.',
            'reimbursement_level.enum' => 'Wybrany poziom odpłatności jest nieprawidłowy.',
            'external_reference.string' => 'Pole external_reference musi być tekstem.',
        ];
    }
}
