<?php

namespace App\Http\Requests;

use App\Enums\SearchModuleType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Summary of GlobalSearchRequest
 */
class GlobalSearchRequest extends FormRequest
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
            'searchString' => ['nullable', 'string', 'max:255'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::enum(SearchModuleType::class)],
        ];
    }
}
