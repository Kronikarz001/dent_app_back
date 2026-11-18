<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\User;
use App\Search\DepartmentSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of DepartmentRepository
 *
 * @extends SearchableRepository<Department>
 */
class DepartmentRepository extends SearchableRepository implements DepartmentRepositoryInterface
{
    /**
     * @var class-string<Department>
     */
    protected string $modelClass = Department::class;

    /**
     * @param DepartmentSearch $search
     */
    public function __construct(
        private readonly DepartmentSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param Department $department
     * @param User $user
     * @return bool
     */
    public function isManager(Department $department, User $user): bool
    {
        $isDirectManager = $department->users()
            ->where('users.uuid', $user->uuid)
            ->wherePivot('is_manager', true)
            ->exists();

        return $isDirectManager || $department->roles()
            ->whereHas('users', fn (Builder $query) => $query->where('users.uuid', $user->uuid)->where('role_user.is_manager', true))
            ->exists();
    }

    /**
     * @param Department $department
     * @param string $userUuid
     * @return bool
     */
    public function hasMember(Department $department, string $userUuid): bool
    {
        return $department->users()->where('users.uuid', $userUuid)->exists()
            || $department->roles()->whereHas('users', fn (Builder $query) => $query->where('users.uuid', $userUuid))->exists();
    }
}
