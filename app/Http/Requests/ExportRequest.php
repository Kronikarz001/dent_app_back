<?php

namespace App\Http\Requests;


use Illuminate\Contracts\Validation\ValidationRule;

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
                'name' => 'nullable|string',
                'type' => 'required|string|in:csv,xls,xlsx,pdf',
            ]
        );
    }
}
