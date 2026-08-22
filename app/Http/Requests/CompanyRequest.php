<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of CompanyRequest
 */
class CompanyRequest extends FormRequest
{
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
        return [
            'name' => ['required'],
            'regon' => ['required', 'string'],
            'nip' => ['required', 'string'],
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
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa jest wymagana.',
            'regon.required' => 'REGON jest wymagany.',
            'regon.string' => 'REGON musi być tekstem.',
            'nip.required' => 'NIP jest wymagany.',
            'nip.string' => 'NIP musi być tekstem.',
            'address.required' => 'Adres jest wymagany.',
            'province.required' => 'Województwo jest wymagane.',
            'district.required' => 'Powiat jest wymagany.',
            'municipality.required' => 'Gmina jest wymagana.',
        ];
    }
}
