<?php

namespace App\Http\Requests;

use App\Enums\PhoneNumberType;
use App\Rules\PhoneNumberRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string', 'confirmed'],
            'pesel' => ['nullable', 'string', 'size:11', 'unique:users,pesel'],
            'private_email' => ['nullable', 'email', 'unique:users,private_email'],
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
