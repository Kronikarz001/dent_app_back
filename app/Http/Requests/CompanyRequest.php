<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Summary of CompanyRequest
 */
class CompanyRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'regon' => ['required', Rule::unique('companies', 'regon')->ignore($this->route('company'))->whereNull('deleted_at')],
            'nip' => ['required', Rule::unique('companies', 'nip')->ignore($this->route('company'))->whereNull('deleted_at')],
            'address' => ['required'],
            'province' => ['required'],
            'district' => ['required'],
            'municipality' => ['required'],
            'business_form' => ['nullable'],
            'type_of_business' => ['nullable'],
            'form_of_ownership' => ['nullable'],
        ];
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
