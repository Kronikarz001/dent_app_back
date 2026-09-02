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
     * @param Model|User $model
     * @return bool
     */
    public function delete(Model|User $model): bool;

    /**
     * @param string $token
     * @return User|null
     */
    public function getUserByToken(string $token): ?User;

    /**
     * @return User
     */
    public function getLoggedUser(): User;

    /**
     * @param string $userUuid
     * @return User
     */
    public function getUserInformation(string $userUuid): User;

    /**
     * @param string $email
     * @param string|null $ignoreUuid
     * @return bool
     */
    public function existsByEmail(string $email, ?string $ignoreUuid = null): bool;

    /**
     * @param string $privateEmail
     * @param string|null $ignoreUuid
     * @return bool
     */
    public function existsByPrivateEmail(string $privateEmail, ?string $ignoreUuid = null): bool;

    /**
     * @param string $pesel
     * @param string|null $ignoreUuid
     * @return bool
     */
    public function existsByPesel(string $pesel, ?string $ignoreUuid = null): bool;
}
