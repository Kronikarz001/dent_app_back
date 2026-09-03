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
            'type' => ['required', (new Enum(CalendarEventType::class))->only(CalendarEventType::appointmentTypes())],
            'name' => ['nullable'],
            'description' => ['nullable'],
            'date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'no_show' => ['boolean'],
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
            'date.required' => 'Data jest wymagana.',
            'date.date' => 'Data musi być prawidłową datą.',
            'end_date.date' => 'Data zakończenia musi być prawidłową datą.',
            'end_date.after_or_equal' => 'Data zakończenia nie może być wcześniejsza niż data rozpoczęcia.',
            'start_time.date_format' => 'Godzina rozpoczęcia musi być w formacie GG:MM.',
            'end_time.date_format' => 'Godzina zakończenia musi być w formacie GG:MM.',
            'end_time.after' => 'Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.',
            'no_show.boolean' => 'Pole no_show musi być wartością logiczną.',
            'created_by.exists' => 'Wybrany użytkownik nie istnieje.',
            'is_active.boolean' => 'Pole is_active musi być wartością logiczną.',
            'dental_examinations.array' => 'Pole dental_examinations musi być tablicą.',
            'dental_examinations.*.exists' => 'Wybrane badanie stomatologiczne nie istnieje.',
        ];
    }
}
