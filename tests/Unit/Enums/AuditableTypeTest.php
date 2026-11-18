<?php

namespace Tests\Unit\Enums;

use App\Enums\AuditableType;
use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\EmployeeSchedule;
use App\Models\JobPosition;
use App\Models\Material;
use App\Models\Patient;
use App\Models\User;
use App\Traits\Auditable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

/**
 * Summary of AuditableTypeTest
 */
final class AuditableTypeTest extends UnitTestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function casesProvider(): array
    {
        return [
            'user' => ['user', User::class],
            'patient' => ['patient', Patient::class],
            'calendar' => ['calendar', Calendar::class],
            'employee-schedule' => ['employee-schedule', EmployeeSchedule::class],
            'dental-examination' => ['dental-examination', DentalExamination::class],
            'material' => ['material', Material::class],
            'job-position' => ['job-position', JobPosition::class],
        ];
    }

    /**
     * @param string $value
     * @param string $modelClass
     * @return void
     */
    #[DataProvider('casesProvider')]
    public function testValueResolvesToModelClass(string $value, string $modelClass): void
    {
        $this->assertSame($modelClass, AuditableType::from($value)->modelClass());
    }

    /**
     * @return void
     */
    public function testProviderCoversEveryCase(): void
    {
        $this->assertCount(count(AuditableType::cases()), self::casesProvider());
    }

    /**
     * @return void
     */
    public function testEveryModelClassUsesAuditableTrait(): void
    {
        foreach (AuditableType::cases() as $type) {
            $this->assertContains(Auditable::class, class_uses_recursive($type->modelClass()), "{$type->modelClass()} is not auditable.");
        }
    }

    /**
     * @return void
     */
    public function testUnknownValueIsRejected(): void
    {
        $this->assertNull(AuditableType::tryFrom('prescription'));
        $this->assertNull(AuditableType::tryFrom('USER'));
    }
}
