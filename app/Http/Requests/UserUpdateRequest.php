<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumberRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Summary of UserRequest
 */
class UserUpdateRequest extends FormRequest
{
    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', 'confirmed'],
            'pesel' => ['nullable', 'string', 'size:11', Rule::unique('users', 'pesel')->ignore($this->route('user'))],
            'private_email' => ['nullable', 'email', Rule::unique('users', 'private_email')->ignore($this->route('user'))],
            'private_phone_number' => ['nullable', 'string', new PhoneNumberRule],
            'phone_number' => ['nullable', 'string', new PhoneNumberRule],
            'is_active' => ['nullable', 'boolean'],
            'pwz_numer' => ['nullable', 'string'],
            'job_positions' => ['nullable', 'array'],
            'job_positions.*' => ['string', 'exists:job_positions,uuid'],
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
