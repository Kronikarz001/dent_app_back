<?php

namespace App\Enums;

/**
 * Summary of AuditableEventType
 */
enum AuditableEventType: string
{
    case CREATE = 'CREATE';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';

    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::CREATE => 'Dodanie',
            self::UPDATE => 'Aktualizacja',
            self::DELETE => 'Usunięcie',
        };
    }
}
