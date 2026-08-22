<?php

namespace Tests\Unit\Factories;

use App\Models\Company;
use Tests\Unit\UnitTestCase;

/**
 * Summary of CompanyFactoryTest
 */
final class CompanyFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCompanyCreateByFactory(): void
    {
        $company = Company::factory()->create(['name' => 'Testowa Sp. z o.o.']);

        $this->assertEquals('Testowa Sp. z o.o.', $company->name);
    }
}
