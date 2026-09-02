<?php

namespace Tests\Feature\Services;

use App\Enums\PhoneNumberType;
use App\Exceptions\DuplicateUserDataException;
use App\Models\User;
use App\Services\UserServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    /**
     * @var UserServiceInterface|Application|mixed|object
     */
    private UserServiceInterface $service;

    protected const USERS_TABLE = 'users';

    protected const TOKENS_TABLE = 'personal_access_tokens';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetUsersReturnsPaginatedResults(): void
    {
        User::factory()->count(3)->create();

        $result = $this->service->getUsers();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testGetUsersListReturnsPaginator(): void
    {
        User::factory()->count(2)->create();

        $result = $this->service->getUsersList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /**
     * @return void
     */
    public function testCreateUserPersistsToDatabase(): void
    {
        $data = [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@example.com',
            'private_email' => 'jan.private@example.com',
            'password' => Hash::make('password'),
            'pesel' => '90010112345',
            'is_active' => true,
        ];

        $user = $this->service->createUser($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas(self::USERS_TABLE, ['email' => 'jan.kowalski@example.com']);
    }

    /**
     * @return void
     */
    public function testUpdateUserPersistsChangesToDatabase(): void
    {
        $user = User::factory()->create();

        $result = $this->service->updateUser($user, ['first_name' => 'Zmienione']);

        $this->assertInstanceOf(User::class, $result);
        $this->assertDatabaseHas(self::USERS_TABLE, ['uuid' => $user->uuid, 'first_name' => 'Zmienione']);
    }

    /**
     * @return void
     */
    public function testUpdateUserThrowsWhenPrivateEmailAlreadyTakenByAnotherUser(): void
    {
        User::factory()->create(['private_email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->expectException(DuplicateUserDataException::class);

        $this->service->updateUser($user, ['private_email' => 'taken@example.com']);
    }

    /**
     * @return void
     */
    public function testUpdateUserAllowsKeepingOwnEmailUnchanged(): void
    {
        $user = User::factory()->create(['email' => 'own@example.com']);

        $result = $this->service->updateUser($user, ['email' => 'own@example.com']);

        $this->assertSame('own@example.com', $result->email);
    }

    /**
     * @return void
     */
    public function testUpdateUserAssignsPrivateAndWorkPhoneNumbers(): void
    {
        $user = User::factory()->create();

        $this->service->updateUser($user, [
            'private_phone_number' => '48500100200',
            'phone_number' => '48600200300',
        ]);

        $this->assertDatabaseHas('phone_numbers', [
            'phoneable_uuid' => $user->uuid,
            'number' => '48500100200',
            'type' => PhoneNumberType::PRIVATE->value,
        ]);
        $this->assertDatabaseHas('phone_numbers', [
            'phoneable_uuid' => $user->uuid,
            'number' => '48600200300',
            'type' => PhoneNumberType::WORK->value,
        ]);
    }

    /**
     * @return void
     */
    public function testDeactivateUserSetsActiveToFalse(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->service->deactivateUser($user);

        $this->assertDatabaseHas(self::USERS_TABLE, ['uuid' => $user->uuid, 'is_active' => false]);
    }

    /**
     * @return void
     */
    public function testDeleteUserRemovesFromDatabase(): void
    {
        $user = User::factory()->create();

        $this->service->deleteUser($user);

        $this->assertDatabaseMissing(self::USERS_TABLE, ['uuid' => $user->uuid]);
    }

    /**
     * @return void
     */
    public function testEditPasswordHashesAndPersistsNewPassword(): void
    {
        $user = User::factory()->create();
        $plaintext = 'NoweHaslo123!';

        $result = $this->service->editPassword($user, ['password' => $plaintext]);

        $this->assertInstanceOf(User::class, $result);
        $fresh = User::find($user->uuid);
        $this->assertTrue(Hash::check($plaintext, $fresh->password));
    }

    /**
     * @return void
     */
    public function testGetUserInformationReturnsUser(): void
    {
        $user = User::factory()->create();

        $result = $this->service->getUserInformation($user);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user->uuid, $result->uuid);
    }

    /**
     * @return void
     */
    public function testGetUserByTokenReturnsUserForValidToken(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $tokenValue = explode('|', $token)[1];

        $result = $this->service->getUserByToken($tokenValue);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user->uuid, $result->uuid);
    }

    /**
     * @return void
     */
    public function testGetUserByTokenReturnsNullForInvalidToken(): void
    {
        $result = $this->service->getUserByToken('invalid-token-xyz');

        $this->assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetLoggedUserReturnsAuthenticatedUser(): void
    {
        $user = User::factory()->create();
        Auth::setUser($user);

        $result = $this->service->getLoggedUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user->uuid, $result->uuid);
    }
}
