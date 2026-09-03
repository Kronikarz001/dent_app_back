<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Summary of PeselRule
 */
class PeselRule implements ValidationRule
{
    /**
     * @var int[]
     */
    private const WEIGHTS = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];

    /**
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^\d{11}$/', $value)) {
            $fail('PESEL musi składać się z dokładnie 11 cyfr.');

            return;
        }

        $digits = array_map('intval', str_split($value));
        $sum = 0;

        foreach (self::WEIGHTS as $index => $weight) {
            $sum += $weight * $digits[$index];
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        if ($checkDigit !== $digits[10]) {
            $fail('Nieprawidłowy numer PESEL (błędna suma kontrolna).');
        }
    }
}
