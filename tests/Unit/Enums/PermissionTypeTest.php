<?php

namespace Tests\Unit\Enums;

use App\Enums\PermissionType;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PermissionTypeTest
 */
final class PermissionTypeTest extends UnitTestCase
{
    /**
     * @return array<string, array{string, PermissionType}>
     */
    public static function methodsProvider(): array
    {
        return [
            'get' => ['GET', PermissionType::VIEW],
            'head' => ['HEAD', PermissionType::VIEW],
            'options' => ['OPTIONS', PermissionType::VIEW],
            'post' => ['POST', PermissionType::EDIT],
            'put' => ['PUT', PermissionType::EDIT],
            'patch' => ['PATCH', PermissionType::EDIT],
            'delete' => ['DELETE', PermissionType::EDIT],
        ];
    }

    /**
     * @param string $method
     * @param PermissionType $expected
     * @return void
     */
    #[DataProvider('methodsProvider')]
    public function testFromRequestMapsHttpMethod(string $method, PermissionType $expected): void
    {
        $this->assertSame($expected, PermissionType::fromRequest(Request::create('/api/patient', $method)));
    }

    /**
     * @return void
     */
    public function testValues(): void
    {
        $this->assertSame('view', PermissionType::VIEW->value);
        $this->assertSame('edit', PermissionType::EDIT->value);
    }
}
