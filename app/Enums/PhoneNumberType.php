<?php

namespace App\Enums;

/**
 * Summary of PhoneNumberType
 */
enum PhoneNumberType: string
{
    case PRIVATE = 'PRIVATE';
    case WORK = 'WORK';

    public function name(): string
    {
        return match ($this) {
            self::PRIVATE => 'Private',
            self::WORK => 'Work',
        };
    }
}
