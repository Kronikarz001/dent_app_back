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
