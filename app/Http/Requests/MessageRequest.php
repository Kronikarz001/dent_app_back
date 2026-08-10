<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of MessageRequest
 */
class MessageRequest extends FormRequest
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
            'message' => ['required', 'string', 'max:5000'],
            'recipient_uuid' => ['nullable', 'string', 'exists:users,uuid', 'prohibits:message_group_uuid'],
            'message_group_uuid' => ['nullable', 'string', 'exists:message_groups,uuid', 'prohibits:recipient_uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Treść wiadomości jest wymagana.',
            'message.string' => 'Treść wiadomości musi być tekstem.',
            'message.max' => 'Treść wiadomości może mieć maksymalnie 5000 znaków.',
            'recipient_uuid.exists' => 'Wybrany odbiorca nie istnieje.',
            'recipient_uuid.prohibits' => 'Nie można podać jednocześnie odbiorcy i grupy.',
            'message_group_uuid.exists' => 'Wybrana grupa nie istnieje.',
            'message_group_uuid.prohibits' => 'Nie można podać jednocześnie grupy i odbiorcy.',
        ];
    }
}
