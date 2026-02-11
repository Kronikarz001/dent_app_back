<?php

namespace App\Services;

use App\Models\User;
use App\Resources\UserResource;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    /**
     * @return string
     */
    public function getLoggedUserFullName(): string;

    /**
     * @return LengthAwarePaginator
     */
    public function getUsersList(): LengthAwarePaginator;

    /**
     * @param string|null $token
     * @return User|null
     */
    public function getUserByToken(?string $token): ?User;

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User;

    /**
     * @return UserResource
     */
    public function getUserInformation(): UserResource;

    /**
     * @param array $data
     * @return User
     */
    public function editPassword(array $data):User;
}
