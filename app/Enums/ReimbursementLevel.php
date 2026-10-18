<?php

namespace App\Enums;

/**
 * Summary of ReimbursementLevel
 */
enum ReimbursementLevel: string
{
    case FULL = 'FULL';
    case FLAT_RATE = 'FLAT_RATE';
    case FREE = 'FREE';
    case R = 'R';
    case OTHER = 'OTHER';

    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::FULL => 'Pełna odpłatność (100%)',
            self::FLAT_RATE => 'Ryczałt',
            self::FREE => 'Bezpłatny',
            self::R => 'Kategoria R',
            self::OTHER => 'Inne',
        };
    }
}
