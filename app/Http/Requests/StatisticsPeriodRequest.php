<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

/**
 * Summary of StatisticsPeriodRequest
 */
class StatisticsPeriodRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'year' => ['sometimes', 'integer', 'between:2000,2100'],
            'month' => ['sometimes', 'integer', 'between:1,12'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year.integer' => 'Rok musi być liczbą całkowitą.',
            'year.between' => 'Rok musi mieścić się w przedziale 2000-2100.',
            'month.integer' => 'Miesiąc musi być liczbą całkowitą.',
            'month.between' => 'Miesiąc musi mieścić się w przedziale 1-12.',
            'page.integer' => 'Numer strony musi być liczbą całkowitą.',
            'page.min' => 'Numer strony musi być większy od zera.',
            'perPage.integer' => 'Liczba elementów na stronie musi być liczbą całkowitą.',
            'perPage.min' => 'Liczba elementów na stronie musi być większa od zera.',
            'perPage.max' => 'Liczba elementów na stronie nie może przekraczać 500.',
        ];
    }

    /**
     * @return int
     */
    public function year(): int
    {
        return (int) $this->validated('year', Carbon::now()->year);
    }

    /**
     * @return int
     */
    public function month(): int
    {
        return (int) $this->validated('month', Carbon::now()->month);
    }

    /**
     * @return int
     */
    public function page(): int
    {
        return (int) $this->validated('page', 1);
    }

    /**
     * @return int
     */
    public function perPage(): int
    {
        return (int) $this->validated('perPage', config('search.default_per_page'));
    }
}
