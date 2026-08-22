<?php

namespace App\Http\Requests;

use App\Enums\ExportType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Enum;

/**
 * @property string $name
 * @property string $type
 */
class ExportRequest extends DataSortFilterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'name' => ['nullable', 'string'],
                'type' => ['required', 'string', new Enum(ExportType::class)],
            ]
        );
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(
            parent::messages(),
            [
                'name.string' => 'Pole name musi być tekstem.',
                'type.required' => 'Typ eksportu jest wymagany.',
                'type.string' => 'Typ eksportu musi być tekstem.',
                'type.in' => 'Typ eksportu musi być jednym z: csv, xls, xlsx, pdf.',
            ]
        );
    }
}
