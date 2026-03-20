<?php

namespace App\Services;

use App\Models\User;
use App\Resources\UserResource;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of UserServiceInterface
 */
interface UserServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getUsers(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getUsersList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User;

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User;

    /**
     * @param User $user
     * @return void
     */
    public function deactivateUser(User $user): void;

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function editPassword(User $user, array $data): User;

    /**
     * @param User $user
     * @return User
     */
    public function getUserInformation(User $user): User;

    /**
     * @param string $token
     * @return User|null
     */
    public function getUserByToken(string $token): ?User;

    /**
     * @return User
     */
    public function getLoggedUser(): User;
}
