<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of UserRepositoryInterface
 */
interface UserRepositoryInterface extends BasicRepositoryInterface
{
    public function findByUuid(string $uuid): ?User;

    public function create(array $data): User;

    public function update(User|Model $user, array $data): User;

    public function delete(Model|User $model): bool;

    public function getUserByToken(string $token): ?User;

    public function getLoggedUser(): User;

    public function getUserInformation(string $userUuid): User;
}
