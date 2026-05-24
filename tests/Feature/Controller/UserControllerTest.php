<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use Tests\TestCase;

/**
 * Summary of UserControllerTest
 */
class UserControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexUsersReturnSuccessResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
        ->getJson(route('user.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
     public function testIndexUsersListReturnSuccessResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowUserReturnSuccessResponse(): void
    {
        $user = User::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.show', ['user' => $user->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStoreUserReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('user.store'), [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'example@mail',
                'pesel' => '12345678901',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'example@mail',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateUserReturnNoContentResponse(): void
    {
        $user = User::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('user.update', ['user' => $user->uuid]), [
                'first_name' => 'Updated',
                'last_name' => 'User',
                'email' => 'example_updated@mail',
                'pesel' => '12345678901',
            ]);
        $response->assertNoContent();

        $this->assertDatabaseHas('users', [
            'email' => 'example_updated@mail',
        ]);
    }

    /**
     * @return void
     */
    public function testDeleteUserReturnNoContentResponse(): void
    {
        $user = User::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->deleteJson(route('user.destroy', ['user' => $user->uuid]));
        $response->assertNoContent();

        $this->assertDatabaseMissing('users', [
            'email' => 'example@mail',
        ]);
    }

    /**
     * @return void
     */
    public function testShowLoggedUserReturnSuccessResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.user-info'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testExportUserReturnSuccessResponse(): void
    {
        User::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.export', ['type' => 'xlsx']));

        $response->assertOk();
    }
}
