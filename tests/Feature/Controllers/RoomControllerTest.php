<?php

namespace Tests\Feature\Controllers;

use App\Models\Room;
use Tests\TestCase;

/**
 * Summary of RoomControllerTest
 */
class RoomControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsPaginatedRooms(): void
    {
        Room::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('room.index'));

        $response->assertOk();
        $response->assertJsonPath('total', 3);
        $response->assertJsonStructure(['data' => [['uuid', 'name', 'description', 'is_active']], 'current_page', 'last_page']);
    }

    /**
     * @return void
     */
    public function testSelectListReturnsOnlyActiveRoomsAsPlainArray(): void
    {
        $active = Room::factory()->create(['name' => 'Gabinet 1']);
        Room::factory()->create(['name' => 'Gabinet 2', 'is_active' => false]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('room.selectList'));

        $response->assertOk();
        $response->assertExactJson([['uuid' => $active->uuid, 'name' => 'Gabinet 1']]);
    }

    /**
     * @return void
     */
    public function testShowReturnsRoom(): void
    {
        $room = Room::factory()->create();

        $this->callApiWithLoggedUser()
            ->getJson(route('room.show', ['room' => $room->uuid]))
            ->assertOk()
            ->assertJsonPath('uuid', $room->uuid);
    }

    /**
     * @return void
     */
    public function testStoreCreatesRoom(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('room.store'), [
                'name' => 'Gabinet chirurgiczny',
                'description' => 'Parter',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('name', 'Gabinet chirurgiczny');
        $this->assertDatabaseHas('rooms', ['name' => 'Gabinet chirurgiczny', 'is_active' => true]);
    }

    /**
     * @return void
     */
    public function testStoreRejectsDuplicateName(): void
    {
        Room::factory()->create(['name' => 'Gabinet 1']);

        $this->callApiWithLoggedUser()
            ->postJson(route('room.store'), ['name' => 'Gabinet 1'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * @return void
     */
    public function testStoreRequiresName(): void
    {
        $this->callApiWithLoggedUser()
            ->postJson(route('room.store'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * @return void
     */
    public function testUpdateAllowsKeepingOwnName(): void
    {
        $room = Room::factory()->create(['name' => 'Gabinet 1']);

        $this->callApiWithLoggedUser()
            ->putJson(route('room.update', ['room' => $room->uuid]), [
                'name' => 'Gabinet 1',
                'is_active' => false,
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('rooms', ['uuid' => $room->uuid, 'is_active' => false]);
    }

    /**
     * @return void
     */
    public function testDestroyDeletesRoom(): void
    {
        $room = Room::factory()->create();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('room.destroy', ['room' => $room->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('rooms', ['uuid' => $room->uuid]);
    }

    /**
     * @return void
     */
    public function testIndexRequiresAuthentication(): void
    {
        $this->getJson(route('room.index'))->assertUnauthorized();
    }
}
