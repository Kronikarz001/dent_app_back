<?php

namespace Tests\Mocks\Repositories;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorImpl;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of UserMockRepository
 */
class UserMockRepository implements UserRepositoryInterface
{
    /**
     * @param string $uuid
     * @return User|null
     */
    public function findByUuid(string $uuid): ?User
    {
        return new User;
    }

    /**
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::factory()->make($data);
    }

    /**
     * @param User|Model $user
     * @param array $data
     * @return User
     */
    public function update(User|Model $user, array $data): User
    {
        return User::factory()->make($data);
    }

    /**
     * @param Model|User $model
     * @return bool
     */
    public function delete(Model|User $model): bool
    {
        return true;
    }

    /**
     * @param array $uuids
     * @return Collection
     */
    public function findAllByUuids(array $uuids): Collection
    {
        return new Collection;
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return new PaginatorImpl([], 0, 10);
    }

    /**
     * @param array $columns
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return new PaginatorImpl([], 0, 10);
    }

    /**
     * @param string $modelClass
     * @param array $uniqueAttributes
     * @param array $values
     * @return Model
     */
    public function createOrUpdate(string $modelClass, array $uniqueAttributes, array $values): Model
    {
        return $modelClass::updateOrCreate($uniqueAttributes, $values);
    }

    /**
     * @param string $token
     * @return User|null
     */
    public function getUserByToken(string $token): ?User
    {
        return null;
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
        return new User;
    }
}
