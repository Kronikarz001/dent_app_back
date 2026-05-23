<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of JobPositionRequest
 */
class JobPositionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'f_name' => ['required', 'string'],
            'm_name' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
