<?php

namespace Tests\Unit\Enums;

use App\Enums\ExportType;
use App\Mappers\ExportMapper;
use Tests\Unit\UnitTestCase;

/**
 * Summary of ExportTypeTest
 */
final class ExportTypeTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testValuesAreSupportedFileExtensions(): void
    {
        $this->assertSame(['xlsx', 'pdf', 'csv', 'xls'], array_column(ExportType::cases(), 'value'));
    }

    /**
     * @return void
     */
    public function testNameMatchesValue(): void
    {
        foreach (ExportType::cases() as $type) {
            $this->assertSame($type->value, $type->name());
        }
    }

    /**
     * @return void
     */
    public function testEveryTypeIsSupportedByExportMapper(): void
    {
        $mapper = new ExportMapper;

        foreach (ExportType::cases() as $type) {
            $this->assertNotEmpty($mapper->map($type->value));
        }
    }
}
