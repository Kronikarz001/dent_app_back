<?php
namespace App\Repositories;

use App\Search\UserSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
    ) {
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }
}
