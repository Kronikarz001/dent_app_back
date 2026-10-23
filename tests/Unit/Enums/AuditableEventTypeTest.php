<?php

namespace Tests\Unit\Enums;

use App\Enums\AuditableEventType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

/**
 * Summary of AuditableEventTypeTest
 */
final class AuditableEventTypeTest extends UnitTestCase
{
    /**
     * @return array<string, array{AuditableEventType, string, string}>
     */
    public static function casesProvider(): array
    {
        return [
            'create' => [AuditableEventType::CREATE, 'CREATE', 'Dodanie'],
            'update' => [AuditableEventType::UPDATE, 'UPDATE', 'Aktualizacja'],
            'delete' => [AuditableEventType::DELETE, 'DELETE', 'Usunięcie'],
        ];
    }

    /**
     * @param AuditableEventType $type
     * @param string $value
     * @param string $name
     * @return void
     */
    #[DataProvider('casesProvider')]
    public function testCaseHasExpectedValueAndName(AuditableEventType $type, string $value, string $name): void
    {
        $this->assertSame($value, $type->value);
        $this->assertSame($name, $type->name());
        $this->assertSame($type, AuditableEventType::from($value));
    }

    /**
     * @return void
     */
    public function testProviderCoversEveryCase(): void
    {
        $this->assertCount(count(AuditableEventType::cases()), self::casesProvider());
    }
}
