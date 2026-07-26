<?php

namespace App\Enums;

/**
 * Summary of UserStatusType
 */
enum UserStatusType: string
{
    case ACTIVE = 'ACTIVE';
    case NON_ACTIVE = 'NON_ACTIVE';

    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::NON_ACTIVE => 'Non-Active',
        };
    }
}
