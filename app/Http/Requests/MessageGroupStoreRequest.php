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
}
