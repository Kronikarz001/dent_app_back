<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of MessageGroupStoreRequest
 */
class MessageGroupStoreRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'user_uuids' => ['required', 'array', 'min:1'],
            'user_uuids.*' => ['required', 'string', 'exists:users,uuid', 'not_in:'.Auth::user()->uuid],
        ];
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa grupy jest wymagana.',
            'name.string' => 'Nazwa grupy musi być tekstem.',
            'name.max' => 'Nazwa grupy może mieć maksymalnie 255 znaków.',
            'user_uuids.required' => 'Lista uczestników jest wymagana.',
            'user_uuids.array' => 'Pole user_uuids musi być tablicą.',
            'user_uuids.min' => 'Grupa musi zawierać co najmniej jednego uczestnika.',
            'user_uuids.*.required' => 'Identyfikator uczestnika jest wymagany.',
            'user_uuids.*.exists' => 'Wybrany uczestnik nie istnieje.',
            'user_uuids.*.not_in' => 'Nie możesz dodać samego siebie jako uczestnika.',
        ];
    }
}
