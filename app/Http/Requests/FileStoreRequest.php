<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'files'   => ['required', 'array'],
            'files.*' => ['required', 'file'],
        ];
    }
}
