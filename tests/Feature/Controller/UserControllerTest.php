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
}
