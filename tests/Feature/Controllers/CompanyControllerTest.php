<?php

namespace Tests\Feature\Controllers;

use App\Models\Company;
use Tests\TestCase;

/**
 * Summary of CompanyControllerTest
 */
class CompanyControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsSuccessResponse(): void
    {
        Company::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('company.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowCompanyReturnsSuccessResponse(): void
    {
        $company = Company::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('company.show', ['company' => $company->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $company->uuid);
    }

    /**
     * @return void
     */
    public function testStoreCompanyReturnsCreatedResponse(): void
    {
        $data = Company::factory()->make()->toArray();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('company.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('companies', ['nip' => $data['nip']]);
    }

    /**
     * @return void
     */
    public function testStoreCompanyRejectsDuplicateNip(): void
    {
        $existing = Company::factory()->create();
        $data = Company::factory()->make(['nip' => $existing->nip])->toArray();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('company.store'), $data);

        $response->assertUnprocessable();
    }

    /**
     * @return void
     */
    public function testUpdateCompanyAllowsKeepingOwnNip(): void
    {
        $company = Company::factory()->create();
        $data = Company::factory()->make(['nip' => $company->nip])->toArray();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('company.update', ['company' => $company->uuid]), $data);

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testUpdateCompanyReturnsSuccessResponse(): void
    {
        $company = Company::factory()->create();
        $data = Company::factory()->make(['name' => 'Zaktualizowana nazwa'])->toArray();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('company.update', ['company' => $company->uuid]), $data);

        $response->assertOk();
        $this->assertDatabaseHas('companies', ['uuid' => $company->uuid, 'name' => 'Zaktualizowana nazwa']);
    }

    /**
     * @return void
     */
    public function testDestroyCompanyReturnsNoContentResponse(): void
    {
        $company = Company::factory()->create();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('company.destroy', ['company' => $company->uuid]))
            ->assertNoContent();

        $this->assertSoftDeleted('companies', ['uuid' => $company->uuid]);
    }
}
