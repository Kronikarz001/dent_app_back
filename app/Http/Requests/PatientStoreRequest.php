<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of JobPositionRequest
 */
class PatientStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:patients,email'],
            'pesel' => ['required', 'string', 'size:11', 'unique:patients,pesel'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
