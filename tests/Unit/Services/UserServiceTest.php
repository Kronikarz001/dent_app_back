<?php

namespace Tests\Unit\Services;

use App\Exceptions\DuplicateUserDataException;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Services\ExportServiceInterface;
use App\Services\PhoneNumberServiceInterface;
use App\Services\UserService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Summary of UserServiceTest
 */
class UserServiceTest extends TestCase
{
    private MockInterface $userRepository;

    private UserService $userService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $exportService = Mockery::mock(ExportServiceInterface::class);
        $phoneNumberService = Mockery::mock(PhoneNumberServiceInterface::class);
        $this->userService = new UserService($this->userRepository, $exportService, $phoneNumberService);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testGetUsersDelegatesToRepositoryWithoutColumnFilter(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->userRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $result = $this->userService->getUsers();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testGetUsersListPassesOnlyUuidAndNameColumns(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 100, 1);

        $this->userRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(['uuid', 'name'])
            ->andReturn($paginator);

        $result = $this->userService->getUsersList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testGetUsersListDoesNotPassEmptyColumns(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 100, 1);

        $this->userRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(Mockery::not([]))
            ->andReturn($paginator);

        $result = $this->userService->getUsersList();

        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testCreateUserGeneratesRandomPasswordAndPassesRestOfDataUnchanged(): void
    {
        $data = ['name' => 'Jan', 'email' => 'jan@example.com', 'password' => 'plain'];
        $newUser = User::factory()->make(['id' => 1]);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $passedData) {
                $this->assertSame('Jan', $passedData['name']);
                $this->assertSame('jan@example.com', $passedData['email']);
                $this->assertNotSame('plain', $passedData['password']);
                $this->assertSame(32, strlen($passedData['password']));
                $this->assertMatchesRegularExpression('/[a-zA-Z]/', $passedData['password']);
                $this->assertMatchesRegularExpression('/[0-9]/', $passedData['password']);
                $this->assertMatchesRegularExpression('/[^a-zA-Z0-9]/', $passedData['password']);

                return true;
            }))
            ->andReturn($newUser);

        $result = $this->userService->createUser($data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($newUser, $result);
    }

    /**
     * @return void
     */
    public function testUpdateUserPassesUserAndDataToRepository(): void
    {
        $user = User::factory()->make(['id' => 5]);
        $data = ['name' => 'New name'];
        $updatedUser = User::factory()->make(['id' => 5, 'name' => 'New name']);

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with($user, $data)
            ->andReturn($updatedUser);

        $result = $this->userService->updateUser($user, $data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($updatedUser, $result);
    }

    /**
     * @return void
     */
    public function testUpdateUserThrowsWhenEmailAlreadyTakenByAnotherUser(): void
    {
        $user = User::factory()->make(['uuid' => 'user-uuid']);

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->once()
            ->with('taken@example.com', 'user-uuid')
            ->andReturnTrue();

        $this->userRepository->shouldNotReceive('update');

        $this->expectException(DuplicateUserDataException::class);

        $this->userService->updateUser($user, ['email' => 'taken@example.com']);
    }

    /**
     * @return void
     */
    public function testUpdateUserThrowsWhenPeselAlreadyTakenByAnotherUser(): void
    {
        $user = User::factory()->make(['uuid' => 'user-uuid']);

        $this->userRepository
            ->shouldReceive('existsByPesel')
            ->once()
            ->with('12345678901', 'user-uuid')
            ->andReturnTrue();

        $this->userRepository->shouldNotReceive('update');

        $this->expectException(DuplicateUserDataException::class);

        $this->userService->updateUser($user, ['pesel' => '12345678901']);
    }

    /**
     * @return void
     */
    public function testDeactivateUserAlwaysSetsActiveToFalse(): void
    {
        $user = User::factory()->make(['id' => 3, 'active' => true]);
        $capturedPayload = null;

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with($user, Mockery::on(function (array $data) use (&$capturedPayload) {
                $capturedPayload = $data;

                return true;
            }));

        $this->userService->deactivateUser($user);

        $this->assertArrayHasKey('is_active', $capturedPayload);
        $this->assertFalse($capturedPayload['is_active']);
    }

    /**
     * @return void
     */
    public function testDeactivateUserReturnsVoid(): void
    {
        $user = User::factory()->make();

        $this->userRepository->shouldReceive('update')->once();

        $this->assertNull($this->userService->deactivateUser($user));
    }

    /**
     * @return void
     */
    public function testDeleteUserCallsRepositoryDeleteNotUpdate(): void
    {
        $user = User::factory()->make(['uuid' => 8]);

        $this->userRepository
            ->shouldReceive('delete')
            ->once()
            ->with($user);

        $this->userRepository->shouldNotReceive('update');

        $this->assertNull($this->userService->deleteUser($user));
    }

    /**
     * @return void
     */
    public function testEditPasswordHashesPasswordBeforeSaving(): void
    {
        $user = User::factory()->make(['id' => 2]);
        $plaintext = 'NewPassword123!';
        $capturedPayload = null;

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with($user, Mockery::on(function (array $data) use (&$capturedPayload) {
                $capturedPayload = $data;

                return true;
            }))
            ->andReturn($user);

        $this->userService->editPassword($user, ['password' => $plaintext]);

        $this->assertArrayHasKey('password', $capturedPayload);
        $this->assertNotEquals($plaintext, $capturedPayload['password']);
        $this->assertTrue(password_verify($plaintext, $capturedPayload['password']));
    }

    /**
     * @return void
     */
    public function testEditPasswordPreservesOtherDataFields(): void
    {
        $user = User::factory()->make();
        $capturedPayload = null;
        $data = [
            'password' => 'Secret123!',
            'password_updated_at' => '2024-01-01',
        ];

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with($user, Mockery::on(function (array $received) use (&$capturedPayload) {
                $capturedPayload = $received;

                return true;
            }))
            ->andReturn($user);

        $this->userService->editPassword($user, $data);

        $this->assertArrayHasKey('password_updated_at', $capturedPayload);
        $this->assertSame('2024-01-01', $capturedPayload['password_updated_at']);
    }

    /**
     * @return void
     */
    public function testEditPasswordReturnsUpdatedUser(): void
    {
        $user = User::factory()->make(['id' => 10]);

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->andReturn($user);

        $result = $this->userService->editPassword($user, ['password' => 'Abc123!']);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user, $result);
    }

    /**
     * @return void
     */
    public function testGetUserInformationPassesUuidStringNotModelToRepository(): void
    {
        $uuid = 'abc-123-uuid';
        $user = User::factory()->make(['uuid' => $uuid]);
        $fullUser = User::factory()->make(['uuid' => $uuid]);

        $this->userRepository
            ->shouldReceive('getUserInformation')
            ->once()
            ->with($uuid)
            ->andReturn($fullUser);

        $result = $this->userService->getUserInformation($user);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($fullUser, $result);
    }

    /**
     * @return void
     */
    public function testGetUserByTokenReturnsUserWhenFound(): void
    {
        $token = 'valid-token-xyz';
        $user = User::factory()->make();

        $this->userRepository
            ->shouldReceive('getUserByToken')
            ->once()
            ->with($token)
            ->andReturn($user);

        $result = $this->userService->getUserByToken($token);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user, $result);
    }

    /**
     * @return void
     */
    public function testGetUserByTokenReturnsNullWhenNotFound(): void
    {
        $this->userRepository
            ->shouldReceive('getUserByToken')
            ->once()
            ->with('invalid-token')
            ->andReturn(null);

        $result = $this->userService->getUserByToken('invalid-token');

        $this->assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetLoggedUserDelegatesToRepository(): void
    {
        $loggedUser = User::factory()->make(['name' => 'Adam Kowalski']);

        $this->userRepository
            ->shouldReceive('getLoggedUser')
            ->once()
            ->andReturn($loggedUser);

        $result = $this->userService->getLoggedUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($loggedUser, $result);
    }
}
