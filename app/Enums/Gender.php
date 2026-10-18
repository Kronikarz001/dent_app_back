<?php

namespace App\Enums;

/**
 * Summary of Gender
 */
enum Gender: string
{
    case MALE = 'MALE';
    case FEMALE = 'FEMALE';

    /**
     * @param string|null $pesel
     * @return self|null
     */
    public static function fromPesel(?string $pesel): ?self
    {
        if (strlen((string) $pesel) !== 11) {
            return null;
        }

        return ((int) $pesel[9]) % 2 === 0 ? self::FEMALE : self::MALE;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
        };
    }

    /**
     * @return string
     */
    public function code(): string
    {
        return match ($this) {
            self::MALE => 'M',
            self::FEMALE => 'F',
        };
    }
}
