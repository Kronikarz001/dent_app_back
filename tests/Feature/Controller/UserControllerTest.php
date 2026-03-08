<?php

namespace Tests\Feature\Controller;

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
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.show', ['user' => 1]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStoreUserReturnSuccessResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('user.store'), [
                'name' => 'Test User',
                'email' => 'example@mail',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'example@mail',
        ]);
    }
}
