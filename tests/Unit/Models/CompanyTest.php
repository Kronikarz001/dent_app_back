<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use Tests\Unit\UnitTestCase;

/**
 * Summary of CompanyTest
 */
final class CompanyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeleteSoftDeletesTheCompany(): void
    {
        $company = Company::factory()->create();

        $company->delete();

        $this->assertSoftDeleted($company);
    }

    /**
     * @return void
     */
    public function testSoftDeletedCompanyIsExcludedFromDefaultQueries(): void
    {
        $company = Company::factory()->create();
        $company->delete();

        $this->assertNull(Company::find($company->uuid));
        $this->assertNotNull(Company::withTrashed()->find($company->uuid));
    }
}
