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
}
