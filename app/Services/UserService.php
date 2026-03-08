<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Summary of UserService
 */
readonly class UserService implements UserServiceInterface
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
        return $this->userRepository->update($user, $data);
    }

    /**
     * @param User $user
     * @return void
     */
    public function deactivateUser(User $user): void
    {
        $this->userRepository->update($user, ['active' => false]);
    }

    /**
     * @param User $user
     * @return void
     */
    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
    }

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function editPassword(User $user, array $data): User
    {
        $data['password'] = bcrypt($data['password']);
        return $this->userRepository->update($user, $data);
    }

    /**
     * @param User $user
     * @return User
     */
    public function getUserInformation(User $user): User
    {
        return $this->userRepository->getUserInformation();
    }

    /**
     * @param array $data
     * @return User|null
     */
    public function getUserByToken(array $data): ?User
    {
        $token = $data['token'];
        return $this->userRepository->getUserByToken($token);
    }

    /**
     * @return User
     */
    public function getLoggedUser(): User
    {
        return $this->userRepository->getLoggedUserFullName();
    }


}
