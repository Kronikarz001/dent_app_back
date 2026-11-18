<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Patient;
use Tests\TestCase;

/**
 * Summary of SearchPaginationTest
 */
class SearchPaginationTest extends TestCase
{
    /**
     * @return void
     */
    public function testSelectListReturnsOnlySelectedColumns(): void
    {
        Material::factory()->count(2)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.selectList'))
            ->assertOk();

        $this->assertCount(2, $response->json());
        $this->assertSame(['uuid', 'name'], array_keys($response->json('0')));
    }

    /**
     * @return void
     */
    public function testSelectListIsNotPaginated(): void
    {
        config(['search.default_per_page' => 2]);
        Material::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.selectList'))
            ->assertOk();

        $this->assertTrue(array_is_list($response->json()));
        $this->assertCount(3, $response->json());
    }

    /**
     * @return void
     */
    public function testPatientSelectListReturnsNameColumns(): void
    {
        Patient::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.selectList'))
            ->assertOk();

        $this->assertSame(['uuid', 'first_name', 'last_name'], array_keys($response->json('0')));
    }

    /**
     * @return void
     */
    public function testIndexUsesDefaultPerPageWhenNotGiven(): void
    {
        config(['search.default_per_page' => 2]);
        Material::factory()->count(3)->create();

        $this->callApiWithLoggedUser()
            ->getJson(route('material.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('per_page', 2)
            ->assertJsonPath('total', 3);
    }

    /**
     * @return void
     */
    public function testPerPageMinusOneReturnsAllRows(): void
    {
        config(['search.default_per_page' => 2]);
        Material::factory()->count(3)->create();

        $this->callApiWithLoggedUser()
            ->getJson(route('material.index', ['perPage' => -1]))
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /**
     * @return void
     */
    public function testExportIncludesRowsBeyondDefaultPage(): void
    {
        config(['search.default_per_page' => 2]);
        Material::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->get(route('material.export', ['type' => 'csv']))
            ->assertOk();

        $lines = array_filter(explode("\n", trim($response->baseResponse->getFile()->getContent())));

        $this->assertCount(4, $lines);
    }
}
