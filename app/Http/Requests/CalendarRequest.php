<?php

namespace App\Http\Requests;

use App\Enums\CalendarEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Summary of CalendarRequest
 */
class CalendarRequest extends FormRequest
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
            'type' => ['required', new Enum(CalendarEventType::class)],
            'name' => ['nullable'],
            'description' => ['nullable'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['required', 'date'],
            'created_by' => ['nullable', 'exists:users,uuid'],
            'is_active' => ['boolean'],
            'dental_examinations' => ['nullable', 'array'],
            'dental_examinations.*' => ['string', 'exists:dental_examinations,uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Typ jest wymagany.',
            'type.enum' => 'Wybrany typ jest nieprawidłowy.',
            'start_date.date' => 'Data rozpoczęcia musi być prawidłową datą.',
            'end_date.required' => 'Data zakończenia jest wymagana.',
            'end_date.date' => 'Data zakończenia musi być prawidłową datą.',
            'created_by.exists' => 'Wybrany użytkownik nie istnieje.',
            'is_active.boolean' => 'Pole is_active musi być wartością logiczną.',
            'dental_examinations.array' => 'Pole dental_examinations musi być tablicą.',
            'dental_examinations.*.exists' => 'Wybrane badanie stomatologiczne nie istnieje.',
        ];
    }
}
