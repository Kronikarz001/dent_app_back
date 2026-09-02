<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Summary of ZipCodeRule
 */
class ZipCodeRule implements ValidationRule
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

        $pattern = '/^\d{2}-\d{3}$/';

        if (! preg_match($pattern, $value)) {
            $fail("Pole {$attribute} nie jest prawidłowym kodem pocztowym.");
        }
    }
}
