<?php

namespace App\Http\Requests;

use App\Dto\PageableDto;
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
            'filters' => ['nullable', 'array'],
            'sorts' => ['nullable', 'array'],
            'page' => ['nullable', 'int'],
            'perPage' => ['nullable', 'int'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'filters.array' => 'Pole filters musi być tablicą.',
            'sorts.array' => 'Pole sorts musi być tablicą.',
            'page.int' => 'Pole page musi być liczbą całkowitą.',
            'perPage.int' => 'Pole perPage musi być liczbą całkowitą.',
        ];
    }

    /**
     * @return PageableDto
     */
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
