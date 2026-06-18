<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
    public function testIndexFiltersByExactFieldValue(): void
    {
        User::factory()->create(['first_name' => 'Zbigniew']);
        $target = User::factory()->create(['first_name' => 'Unikalniejszy']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.index', ['user' => ['first_name' => 'Unikalniejszy']]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testIndexSortsResultsByField(): void
    {
        User::factory()->create(['first_name' => 'Bartosz']);
        User::factory()->create(['first_name' => 'Adam']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.index', ['sort' => 'first_name,asc']));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('first_name')->all();
        $this->assertSame(collect($names)->sort()->values()->all(), $names);
    }

    /**
     * @return void
     */
    public function testIndexSearchStringMatchesPartialValue(): void
    {
        $target = User::factory()->create(['first_name' => 'Wyjatkowy']);
        User::factory()->create(['first_name' => 'Inny']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.index', ['searchString' => 'Wyjatkowy']));

        $response->assertOk();
        $response->assertJsonPath('data.0.uuid', $target->uuid);
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
                'private_email' => 'example_private@mail',
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

    /**
     * @return void
     */
    public function testEditPasswordReturnNoContentResponse(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

        $response = $this->callApiWithLoggedUser($user)
            ->patchJson(route('user.edit_password'), [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertNoContent();

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    /**
     * @return void
     */
    public function testEditPasswordWithWrongCurrentPasswordReturnsValidationError(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

        $response = $this->callApiWithLoggedUser($user)
            ->patchJson(route('user.edit_password'), [
                'current_password' => 'WrongPassword!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testEditPasswordRequiresAuthentication(): void
    {
        $response = $this->patchJson(route('user.edit_password'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertUnauthorized();
    }
}
