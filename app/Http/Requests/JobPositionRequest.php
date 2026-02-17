<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobPositionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'f_name' => ['required', 'string'],
            'l_name' => ['required'], 'string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
