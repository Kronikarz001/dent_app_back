<?php

namespace App\Http\Requests;

use App\Enums\CalendarEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Summary of EmployeeScheduleRequest
 */
class EmployeeScheduleRequest extends FormRequest
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
            'type' => ['required', (new Enum(CalendarEventType::class))->only(CalendarEventType::employeeTypes())],
            'name' => ['nullable'],
            'description' => ['nullable'],
            'date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'is_active' => ['boolean'],
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
            'start_time.date_format' => 'Godzina rozpoczęcia musi być w formacie GG:MM.',
            'end_time.date_format' => 'Godzina zakończenia musi być w formacie GG:MM.',
            'is_active.boolean' => 'Pole is_active musi być wartością logiczną.',
        ];
    }
}
