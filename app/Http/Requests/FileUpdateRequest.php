<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'filename' => ['required', 'string', 'max:255'],
        ];
    }
}
