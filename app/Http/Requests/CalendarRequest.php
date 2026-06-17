<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalendarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required'],
            'name' => ['nullable'],
            'description' => ['nullable'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['required', 'date'],
            'created_by' => ['nullable', 'exists:users,uuid'],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
