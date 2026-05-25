<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Summary of PhoneNumberRule
 */
class PhoneNumberRule implements ValidationRule
{
    /**
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $normalized = preg_replace('/[\s\-()]/', '', $value);

        $pattern = '/^(?:48[0-9]{9}|49[0-9]{7,11})$/';

        if (! preg_match($pattern, $normalized)) {
            $fail('Invalid phone number');
        }
    }
}
