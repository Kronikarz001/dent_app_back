<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of UpdateNotificationPreferencesRequest
 */
class UpdateNotificationPreferencesRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferences' => ['required', 'array', 'min:1'],
            'preferences.*.type_id' => ['required', 'uuid', 'exists:notification_types,uuid'],
            'preferences.*.channel_id' => ['required', 'uuid', 'exists:notification_channels,uuid'],
            'preferences.*.enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'preferences.required' => 'Pole preferences jest wymagane.',
            'preferences.array' => 'Pole preferences musi być tablicą.',
            'preferences.min' => 'Pole preferences musi zawierać co najmniej jeden element.',
            'preferences.*.type_id.required' => 'Identyfikator typu powiadomienia jest wymagany.',
            'preferences.*.type_id.uuid' => 'Identyfikator typu powiadomienia musi być prawidłowym UUID.',
            'preferences.*.type_id.exists' => 'Wybrany typ powiadomienia nie istnieje.',
            'preferences.*.channel_id.required' => 'Identyfikator kanału powiadomień jest wymagany.',
            'preferences.*.channel_id.uuid' => 'Identyfikator kanału powiadomień musi być prawidłowym UUID.',
            'preferences.*.channel_id.exists' => 'Wybrany kanał powiadomień nie istnieje.',
            'preferences.*.enabled.required' => 'Pole enabled jest wymagane.',
            'preferences.*.enabled.boolean' => 'Pole enabled musi być wartością logiczną.',
        ];
    }
}
