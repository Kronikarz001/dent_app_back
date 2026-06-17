<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of UserRequest
 */
class UserStoreRequest extends FormRequest
{
    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed'],
            'pesel' => ['required', 'string', 'size:11', 'unique:users,pesel'],
            'private_email' => ['required', 'email', 'unique:users,private_email'],
            'pwz_numer' => ['nullable', 'string'],
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
