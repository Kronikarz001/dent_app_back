<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of UserService
 */
class UserService implements UserServiceInterface
{
    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        private UserRepositoryInterface $userRepository
    )
    {
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getUsers(): LengthAwarePaginator
    {
        return $this->userRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getUsersList(): LengthAwarePaginator
    {
        return $this->userRepository->findAllWithPagination(['id', 'name']);
    }

    /**
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return $this->userRepository->create($data);
    }

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        return $this->userRepository->updateUser($user, $data);
    }

    /**
     * @param User $user
     * @return void
     */
    public function deactivateUser(User $user): void
    {
        return $this->userRepository->deactivateUser($user);
    }

    /**
     * @param array $data
     * @return User
     */
    public function editPassword(array $data): User
    {
        return $this->userRepository->editPassword($data);
    }

    /**
     * @return User
     */
    public function getUserInformation(): User
    {
        return $this->userRepository->getUserInformation();
    }

    /**
     * @param string|null $token
     * @return User|null
     */
    public function getUserByToken(?string $token): ?User
    {
        return $this->userRepository->getUserByToken($token);
    }

    /**
     * @return string
     */
    public function getLoggedUserFullName(): string
    {
        return $this->userRepository->getLoggedUserFullName();
    }


}
