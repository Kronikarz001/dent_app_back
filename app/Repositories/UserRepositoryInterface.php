<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of UserRepositoryInterface
 */
interface UserRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return User|null
     */
    public function findByUuid(string $uuid): ?User;

    /**
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * @param User|Model $user
     * @param array $data
     * @return User
     */
    public function update(User|Model $user, array $data): User;

    /**
     * @param User|Model $user
     * @return void
     */
    public function delete(User|Model $user): void;

    /**
     * @param string $token
     * @return User|null
     */
    public function getUserByToken(string $token): ?User;

    /**
     * @return User
     */
    public function getLoggedUserFullName(): User;
}
