<?php

namespace App\Http\Requests;

use App\Models\MessageGroup;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'message' => ['required', 'string', 'max:5000'],
            'recipient_uuid' => ['nullable', 'string', 'exists:users,uuid', 'prohibits:message_group_uuid'],
            'message_group_uuid' => [
                'nullable',
                'string',
                'exists:message_groups,uuid',
                'prohibits:recipient_uuid',
                $this->senderIsGroupMember(),
            ],
        ];
    }

    /**
     * @return Closure
     */
    private function senderIsGroupMember(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $isMember = MessageGroup::query()
                ->where('uuid', $value)
                ->whereHas('users', fn ($query) => $query->where('users.uuid', Auth::id()))
                ->exists();

            if (! $isMember) {
                $fail('Nie jesteś członkiem tej grupy.');
            }
        };
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
