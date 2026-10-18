<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Tests\TestCase;

/**
 * Summary of UserRepositoryTest
 */
class UserRepositoryTest extends TestCase
{
    /**
     * @var UserRepositoryInterface
     */
    private UserRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(UserRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testGetUserByTokenReturnsOwningUser(): void
    {
        $user = User::factory()->create();
        $plainTextToken = $user->createToken('test-token')->plainTextToken;

        $resolved = $this->repository->getUserByToken($plainTextToken);

        $this->assertTrue($resolved->is($user));
    }

    /**
     * @return void
     */
    public function testGetUserByTokenReturnsNullForUnknownToken(): void
    {
        $this->assertNull($this->repository->getUserByToken('unknown-token'));
    }

    /**
     * @return void
     */
    public function testGetLoggedUserReturnsAuthenticatedUser(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->assertTrue($this->repository->getLoggedUser()->is($user));
    }

    /**
     * @return void
     */
    public function testGetUserInformationReturnsMatchingUser(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->repository->getUserInformation($user->uuid)->is($user));
    }

    /**
     * @return void
     */
    public function testExistsByEmailIgnoresGivenUuid(): void
    {
        $user = User::factory()->create(['email' => 'taken@example.com']);

        $this->assertTrue($this->repository->existsByEmail('taken@example.com'));
        $this->assertFalse($this->repository->existsByEmail('taken@example.com', $user->uuid));
    }

    /**
     * @return void
     */
    public function testExistsByPrivateEmailIgnoresGivenUuid(): void
    {
        $user = User::factory()->create(['private_email' => 'private@example.com']);

        $this->assertTrue($this->repository->existsByPrivateEmail('private@example.com'));
        $this->assertFalse($this->repository->existsByPrivateEmail('private@example.com', $user->uuid));
    }

    /**
     * @return void
     */
    public function testExistsByPeselIgnoresGivenUuid(): void
    {
        $user = User::factory()->create(['pesel' => '90010112345']);

        $this->assertTrue($this->repository->existsByPesel('90010112345'));
        $this->assertFalse($this->repository->existsByPesel('90010112345', $user->uuid));
    }
}
