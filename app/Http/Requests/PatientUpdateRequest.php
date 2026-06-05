<?php

namespace App\Http\Requests;

use App\Enums\PhoneNumberType;
use App\Rules\PhoneNumberRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Summary of JobPositionRequest
 */
class PatientUpdateRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:patients,email'],
            'phone_numbers' => ['nullable', 'array'],
            'phone_numbers.number' => ['string', new PhoneNumberRule],
            'phone_numbers.type' => ['string', new Enum(PhoneNumberType::class)],
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
