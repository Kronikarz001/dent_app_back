<?php

namespace Tests\Feature\Controllers;

use App\Enums\SearchModuleType;
use App\Models\Material;
use App\Models\Patient;
use Tests\TestCase;

/**
 * Summary of SearchControllerTest
 */
class SearchControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testModulesEndpointReturnsModulesWithLabels(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('search.modules'));

        $response->assertOk();
        $response->assertJsonCount(count(SearchModuleType::cases()), 'modules');
        $response->assertJsonFragment(['value' => 'patients', 'label' => 'Pacjenci']);
    }

    /**
     * @return void
     */
    public function testGlobalSearchReturnsPaginatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('search.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'total',
            'per_page',
            'current_page',
            'last_page',
        ]);
    }

    /**
     * @return void
     */
    public function testGlobalSearchReturnsNameDescriptionAndLink(): void
    {
        Material::factory()->create(['name' => 'Zxqwunikat']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('search.index', ['searchString' => 'Zxqwunikat']));

        $response->assertOk();

        $item = collect($response->json('data'))
            ->firstWhere('name', 'Zxqwunikat');

        $this->assertNotNull($item);
        $this->assertArrayHasKey('description', $item);
        $this->assertNotNull($item['link']);
        $this->assertStringContainsString('/api/material/', $item['link']);
    }

    /**
     * @return void
     */
    public function testGlobalSearchCanBeFilteredByModule(): void
    {
        Patient::factory()->create(['first_name' => 'Zxqwunikat']);
        Material::factory()->create(['name' => 'Zxqwunikat']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('search.index', [
                'searchString' => 'Zxqwunikat',
                'modules' => ['materials'],
            ]));

        $response->assertOk();
        $response->assertJsonPath('total', 1);
        $response->assertJsonPath('data.0.name', 'Zxqwunikat');
        $response->assertJsonPath('data.0.link', route('material.show', [Material::query()->firstOrFail()->uuid]));
    }

    /**
     * @return void
     */
    public function testGlobalSearchReturnsNullLinkWhenNoShowRoute(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('search.index', ['modules' => ['messages']]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testSearchRejectsInvalidModule(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('search.index', ['modules' => ['not_a_module']]));

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testSearchRequiresAuthentication(): void
    {
        $this->getJson(route('search.index'))->assertUnauthorized();
    }
}
