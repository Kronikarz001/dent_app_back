<?php

namespace App\Repositories;

use App\Models\User;

/**
 * Summary of UserRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<User>
 */
interface UserRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param string $token
     * @return User|null
     */
    public function getUserByToken(string $token): ?User;

    /**
     * @param User $user
     * @param string $name
     * @return string
     */
    public function createToken(User $user, string $name): string;

    /**
     * @param User $user
     * @return void
     */
    public function revokeCurrentToken(User $user): void;

    /**
     * @return User
     */
    public function getLoggedUser(): User;

    /**
     * @param string $userUuid
     * @return User
     */
    public function getUserInformation(string $userUuid): User;
}
