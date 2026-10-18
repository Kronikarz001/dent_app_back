<?php

namespace App\Enums;

/**
 * Summary of PrescriptionStatus
 */
enum PrescriptionStatus: string
{
    case DRAFT = 'DRAFT';
    case ISSUED = 'ISSUED';
    case REALIZED = 'REALIZED';
    case CANCELLED = 'CANCELLED';
    case EXPIRED = 'EXPIRED';

    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::DRAFT => 'Wersja robocza',
            self::ISSUED => 'Wystawiona',
            self::REALIZED => 'Zrealizowana',
            self::CANCELLED => 'Anulowana',
            self::EXPIRED => 'Wygasła',
        };
    }
}
