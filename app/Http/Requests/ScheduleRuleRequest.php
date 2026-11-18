<?php

namespace App\Http\Requests;

use App\Enums\DayOfWeek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Summary of ScheduleRuleRequest
 */
class ScheduleRuleRequest extends FormRequest
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
            'day' => ['required', new Enum(DayOfWeek::class)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'day.required' => 'Dzień tygodnia jest wymagany.',
            'day.enum' => 'Wybrany dzień tygodnia jest nieprawidłowy.',
            'start_time.required' => 'Godzina rozpoczęcia jest wymagana.',
            'start_time.date_format' => 'Godzina rozpoczęcia musi mieć format GG:MM.',
            'end_time.required' => 'Godzina zakończenia jest wymagana.',
            'end_time.date_format' => 'Godzina zakończenia musi mieć format GG:MM.',
            'end_time.after' => 'Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.',
        ];
    }
}
