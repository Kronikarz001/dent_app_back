<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of MessageRequest
 */
class MessageRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string'],
            'recipient_uuid' => ['nullable', 'string', 'exists:users,uuid', 'prohibits:message_group_uuid'],
            'message_group_uuid' => ['nullable', 'string', 'exists:message_groups,uuid', 'prohibits:recipient_uuid'],
        ];
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
