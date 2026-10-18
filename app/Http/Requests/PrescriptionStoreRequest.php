<?php

namespace App\Http\Requests;

use App\Enums\PrescriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Summary of PrescriptionStoreRequest
 */
class PrescriptionStoreRequest extends FormRequest
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
            'name' => ['nullable', 'string'],
            'code' => ['nullable', 'string', 'unique:prescriptions,code'],
            'status' => ['required', new Enum(PrescriptionStatus::class)],
            'patient_uuid' => ['required', 'string', 'exists:patients,uuid'],
            'doctor_uuid' => ['required', 'string', 'exists:users,uuid'],
            'external_reference' => ['nullable', 'string'],
            'medicines' => ['nullable', 'array'],
            'medicines.*.medicine_uuid' => ['required_with:medicines', 'string', 'exists:medicines,uuid'],
            'medicines.*.quantity' => ['required_with:medicines', 'integer', 'min:1'],
            'medicines.*.dosage' => ['required_with:medicines', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Nazwa musi być tekstem.',
            'code.string' => 'Kod recepty musi być tekstem.',
            'code.unique' => 'Recepta o podanym kodzie już istnieje.',
            'status.required' => 'Status recepty jest wymagany.',
            'status.enum' => 'Wybrany status recepty jest nieprawidłowy.',
            'patient_uuid.required' => 'Pacjent jest wymagany.',
            'patient_uuid.exists' => 'Wybrany pacjent nie istnieje.',
            'doctor_uuid.required' => 'Lekarz jest wymagany.',
            'doctor_uuid.exists' => 'Wybrany lekarz nie istnieje.',
            'external_reference.string' => 'Pole external_reference musi być tekstem.',
            'medicines.array' => 'Pole medicines musi być tablicą.',
            'medicines.*.medicine_uuid.required_with' => 'Lek jest wymagany.',
            'medicines.*.medicine_uuid.exists' => 'Wybrany lek nie istnieje.',
            'medicines.*.quantity.required_with' => 'Ilość jest wymagana.',
            'medicines.*.quantity.integer' => 'Ilość musi być liczbą całkowitą.',
            'medicines.*.quantity.min' => 'Ilość musi być większa od zera.',
            'medicines.*.dosage.required_with' => 'Dawkowanie jest wymagane.',
            'medicines.*.dosage.string' => 'Dawkowanie musi być tekstem.',
        ];
    }
}
