<?php

namespace App\Repositories;

use App\Models\User;
use App\Search\Search;
use App\Search\UserSearch;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of UserRepository
 *
 * @extends SearchableRepository<User>
 */
class UserRepository extends SearchableRepository implements UserRepositoryInterface
{
    /**
     * @var class-string<User>
     */
    protected string $modelClass = User::class;

    /**
     * @param UserSearch $search
     */
    public function __construct(
        private readonly UserSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
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
     * @param User $user
     * @param string $name
     * @return string
     */
    public function createToken(User $user, string $name): string
    {
        return $user->createToken($name)->plainTextToken;
    }

    /**
     * @param User $user
     * @return void
     */
    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
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
        return $this->query()->findOrFail($userUuid);
    }
}
