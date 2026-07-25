<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of CompanyRequest
 */
class CompanyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'regon' => ['required'],
            'nip' => ['required'],
            'address' => ['required'],
            'province' => ['required'],
            'district' => ['required'],
            'municipality' => ['required'],
            'business_form' => ['nullable'],
            'type_of_business' => ['nullable'],
            'form_of_ownership' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
