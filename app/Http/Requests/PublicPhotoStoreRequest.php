<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicPhotoStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'files'         => ['required', 'array'],
            'files.*'       => ['required', 'file'],
            'fileable_type' => ['required', 'string'],
            'fileable_id'   => ['required', 'string'],
        ];
    }
}
