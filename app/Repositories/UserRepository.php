<?php

namespace App\Repositories;

use App\Models\User;
use App\Search\Search;
use App\Search\UserSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserRepository extends SearchableRepository implements UserRepositoryInterface
{
    protected string $modelClass = User::class;

    public function __construct(
        private readonly UserSearch $search
    ) {}

    protected function getSearchModel(): Search
    {
        return $this->search;
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
     * @return User
     */
    public function getLoggedUser(): User
    {
        return Auth::user();
    }
}
