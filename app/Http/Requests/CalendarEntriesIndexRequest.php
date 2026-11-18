<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of CalendarEntriesIndexRequest
 */
abstract class CalendarEntriesIndexRequest extends FormRequest
{
    /**
     * @return string
     */
    abstract protected function prefix(): string;

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
        $prefix = $this->prefix();
        $dateToRules = ['sometimes', 'date_format:Y-m-d'];

        if ($this->filled($prefix.'.date_from')) {
            $dateToRules[] = 'after_or_equal:'.$prefix.'.date_from';
        }

        return [
            $prefix => ['sometimes', 'array'],
            $prefix.'.user_uuids' => ['sometimes', 'array'],
            $prefix.'.user_uuids.*' => ['string', 'uuid'],
            $prefix.'.date_from' => ['sometimes', 'date_format:Y-m-d'],
            $prefix.'.date_to' => $dateToRules,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $prefix = $this->prefix();

        return [
            $prefix.'.user_uuids.array' => 'Pole user_uuids musi być tablicą.',
            $prefix.'.user_uuids.*.uuid' => 'Identyfikator użytkownika musi być poprawnym UUID.',
            $prefix.'.date_from.date_format' => 'Data początkowa musi mieć format RRRR-MM-DD.',
            $prefix.'.date_to.date_format' => 'Data końcowa musi mieć format RRRR-MM-DD.',
            $prefix.'.date_to.after_or_equal' => 'Data końcowa nie może być wcześniejsza niż początkowa.',
        ];
    }

    /**
     * @return array{user_uuids?: string[], date_from?: string, date_to?: string}
     */
    public function filters(): array
    {
        return array_intersect_key(
            (array) $this->validated($this->prefix(), []),
            array_flip(['user_uuids', 'date_from', 'date_to'])
        );
    }
}
