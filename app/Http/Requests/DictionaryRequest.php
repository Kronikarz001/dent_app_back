<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of DictionaryRequest
 */
class DictionaryRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'key' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:512'],
            'additional' => ['nullable'],
        ];
    }
}
