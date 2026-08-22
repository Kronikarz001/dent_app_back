<?php

namespace Tests\Feature\Controllers;

use App\Models\Permission;
use Tests\TestCase;

/**
 * Summary of PermissionControllerTest
 */
class PermissionControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsSuccessResponse(): void
    {
        Permission::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('permission.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testSelectListReturnsSuccessResponse(): void
    {
        Permission::factory()->count(2)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('permission.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowReturnsSuccessResponse(): void
    {
        $permission = Permission::factory()->create(['resource' => 'permtest-widget', 'type' => 'view']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('permission.show', ['permission' => $permission->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $permission->uuid);
        $response->assertJsonPath('name', 'permtest-widget.view');
    }
}
