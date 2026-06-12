<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\UserServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UserServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserServiceInterface::class);
    }

    public function testGetUsersReturnsPaginatedResults(): void
    {
        User::factory()->count(3)->create();

        $result = $this->service->getUsers();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    public function testGetUsersListReturnsPaginator(): void
    {
        User::factory()->count(2)->create();

        $result = $this->service->getUsersList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function testCreateUserPersistsToDatabase(): void
    {
        $data = User::factory()->make()->toArray();
        $data['password'] = Hash::make('password');

        $user = $this->service->createUser($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => $data['email']]);
    }

    public function testUpdateUserPersistsChangesToDatabase(): void
    {
        $user = User::factory()->create();

        $result = $this->service->updateUser($user, ['first_name' => 'Zmienione']);

        $this->assertInstanceOf(User::class, $result);
        $this->assertDatabaseHas('users', ['uuid' => $user->uuid, 'first_name' => 'Zmienione']);
    }

    public function testDeactivateUserSetsActiveToFalse(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->service->deactivateUser($user);

        $this->assertDatabaseHas('users', ['uuid' => $user->uuid, 'is_active' => false]);
    }

    public function testDeleteUserRemovesFromDatabase(): void
    {
        $user = User::factory()->create();

        $this->service->deleteUser($user);

        $this->assertDatabaseMissing('users', ['uuid' => $user->uuid]);
    }

    public function testEditPasswordHashesAndPersistsNewPassword(): void
    {
        $user = User::factory()->create();
        $plaintext = 'NoweHaslo123!';

        $result = $this->service->editPassword($user, ['password' => $plaintext]);

        $this->assertInstanceOf(User::class, $result);
        $fresh = User::find($user->uuid);
        $this->assertTrue(Hash::check($plaintext, $fresh->password));
    }

    public function testGetUserInformationReturnsUser(): void
    {
        $user = User::factory()->create();

        $result = $this->service->getUserInformation($user);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user->uuid, $result->uuid);
    }

    public function testGetUserByTokenReturnsUserForValidToken(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $tokenValue = explode('|', $token)[1];

        $result = $this->service->getUserByToken($tokenValue);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user->uuid, $result->uuid);
    }

    public function testGetUserByTokenReturnsNullForInvalidToken(): void
    {
        $result = $this->service->getUserByToken('invalid-token-xyz');

        $this->assertNull($result);
    }

    public function testGetLoggedUserReturnsAuthenticatedUser(): void
    {
        $user = User::factory()->create();
        Auth::setUser($user);

        $result = $this->service->getLoggedUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user->uuid, $result->uuid);
    }
}
