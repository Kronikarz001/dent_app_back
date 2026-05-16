<?php

namespace App\Repositories;

use App\Models\User;
use App\Search\UserSearch;
use App\Search\Search;
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

    public function findByUuid(string $uuid): ?User
    {
        return User::where('uuid', $uuid)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User|Model $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User|Model $model): bool
    {
        return $model->delete();
    }

    public function getUserByToken(string $token): ?User
    {
        return User::whereToken($token);
    }

    public function getLoggedUser(): User
    {
        return Auth::user();
    }

    public function getUserInformation(string $userUuid): User
    {
        return User::find($userUuid);
    }
}