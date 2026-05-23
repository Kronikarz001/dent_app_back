<?php

namespace App\Repositories;

use App\Models\User;
use App\Search\UserSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of UserRepository
 */
readonly class UserRepository implements UserRepositoryInterface
{
    /**
     * @param UserSearch $search
     */
    public function __construct(
        private UserSearch $search
    ) {}

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @param array $columns
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @param string $uuid
     * @return User|null
     */
    public function findByUuid(string $uuid): ?User
    {
        return User::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * @param User|Model $user
     * @param array $data
     * @return User
     */
    public function update(User|Model $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    /**
     * @param User|Model $model
     * @return bool
     */
    public function delete(User|Model $model): bool
    {
        return $model->delete();
    }

    /**
     * @param string $token
     * @return User|null
     */
    public function getUserByToken(string $token): ?User
    {
        return User::whereToken($token);
    }

    /**
     * @return User
     */
    public function getLoggedUser(): User
    {
        return Auth::user();
    }

    /**
     * @param string $userUuid
     * @return User
     */
    public function getUserInformation(string $userUuid): User
    {
        return User::find($userUuid);
    }
}
