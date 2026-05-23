<?php

namespace App\Http\Requests;

use App\DTO\PageableDto;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @property array|null $filters
 * @property array|null $sorts
 * @property int|null $page
 * @property int|null $perPage
 */
class DataSortFilterRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'filters' => 'nullable|array',
            'sorts' => 'nullable|array',
            'page' => 'nullable|int',
            'perPage' => 'nullable|int',
        ];
    }

    public function getDto(): PageableDto
    {
        return new PageableDto(
            $this->filters ?? [],
            $this->sorts ?? [],
            $this->page ?? 1,
            $this->perPage ?? 10
        );
    }
}
