<?php

namespace App\Repositories;

use App\Enums\PermissionType;
use App\Models\Department;
use App\Models\JobPosition;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use App\Search\PermissionSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Summary of PermissionRepository
 */
class PermissionRepository extends SearchableRepository implements PermissionRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Permission::class;

    /**
     * @param PermissionSearch $search
     */
    public function __construct(
        private readonly PermissionSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param string $uuid
     * @return Permission|null
     */
    public function findByUuid(string $uuid): ?Permission
    {
        return Permission::where('uuid', $uuid)->first();
    }

    /**
     * @param User $user
     * @param string $resource
     * @param PermissionType $type
     * @return bool
     */
    public function hasPermission(User $user, string $resource, PermissionType $type): bool
    {
        $permission = Permission::query()
            ->where('resource', $resource)
            ->where('type', $type->value)
            ->first();

        if ($permission === null) {
            return false;
        }

        return $this->hasPermissionGrant($user, $permission);
    }

    /**
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function hasPermissionGrant(User $user, Permission $permission): bool
    {
        $groupUuids = $permission->permissionGroups()->pluck('permission_groups.uuid');

        return $this->assignableReachableQuery($user)
            ->where(function (Builder $query) use ($permission, $groupUuids) {
                $query->where(fn (Builder $q) => $q->where('grantable_type', Permission::class)->where('grantable_id', $permission->uuid))
                    ->orWhere(fn (Builder $q) => $q->where('grantable_type', PermissionGroup::class)->whereIn('grantable_id', $groupUuids));
            })
            ->exists();
    }

    /**
     * @param User $user
     * @param PermissionGroup $group
     * @return bool
     */
    public function hasGroupGrant(User $user, PermissionGroup $group): bool
    {
        return $this->assignableReachableQuery($user)
            ->where('grantable_type', PermissionGroup::class)
            ->where('grantable_id', $group->uuid)
            ->exists();
    }

    /**
     * @param string $assignableType
     * @param string $assignableUuid
     * @param Permission $permission
     * @return bool
     */
    public function hasContainerPermissionGrant(string $assignableType, string $assignableUuid, Permission $permission): bool
    {
        $groupUuids = $permission->permissionGroups()->pluck('permission_groups.uuid');

        return PermissionAssignment::query()
            ->notExpired()
            ->where('assignable_type', $assignableType)
            ->where('assignable_id', $assignableUuid)
            ->where(function (Builder $query) use ($permission, $groupUuids) {
                $query->where(fn (Builder $q) => $q->where('grantable_type', Permission::class)->where('grantable_id', $permission->uuid))
                    ->orWhere(fn (Builder $q) => $q->where('grantable_type', PermissionGroup::class)->whereIn('grantable_id', $groupUuids));
            })
            ->exists();
    }

    /**
     * @param string $assignableType
     * @param string $assignableUuid
     * @param PermissionGroup $group
     * @return bool
     */
    public function hasContainerGroupGrant(string $assignableType, string $assignableUuid, PermissionGroup $group): bool
    {
        return PermissionAssignment::query()
            ->notExpired()
            ->where('assignable_type', $assignableType)
            ->where('assignable_id', $assignableUuid)
            ->where('grantable_type', PermissionGroup::class)
            ->where('grantable_id', $group->uuid)
            ->exists();
    }

    /**
     * @param User $user
     * @return string[]
     */
    public function getUserPermissionNames(User $user): array
    {
        if ($user->is_admin) {
            return Permission::all()->map(fn (Permission $permission) => $permission->name)->values()->all();
        }

        $assignments = $this->assignableReachableQuery($user)->get();

        $directPermissionUuids = $assignments->where('grantable_type', Permission::class)->pluck('grantable_id');
        $permissionGroupUuids = $assignments->where('grantable_type', PermissionGroup::class)->pluck('grantable_id');

        $viaGroupsPermissionUuids = PermissionGroup::query()
            ->whereIn('uuid', $permissionGroupUuids)
            ->with('permissions')
            ->get()
            ->flatMap(fn (PermissionGroup $group) => $group->permissions->pluck('uuid'));

        $allUuids = $directPermissionUuids->merge($viaGroupsPermissionUuids)->unique();

        return Permission::whereIn('uuid', $allUuids)->get()->map(fn (Permission $permission) => $permission->name)->values()->all();
    }

    /**
     * @param User $user
     * @return Builder
     */
    private function assignableReachableQuery(User $user): Builder
    {
        $roleUuids = $user->roles()->pluck('roles.uuid');
        $jobPositionUuids = $user->jobPositions()->pluck('job_positions.uuid');
        $userGroupUuids = $this->containerUuidsFor(UserGroup::class, $user->userGroups(), $roleUuids, $jobPositionUuids);
        $departmentUuids = $this->containerUuidsFor(Department::class, $user->departments(), $roleUuids, $jobPositionUuids);

        return PermissionAssignment::query()
            ->notExpired()
            ->where(function (Builder $query) use ($user, $userGroupUuids, $roleUuids, $jobPositionUuids, $departmentUuids) {
                $query->where(fn (Builder $q) => $q->where('assignable_type', User::class)->where('assignable_id', $user->uuid))
                    ->orWhere(fn (Builder $q) => $q->where('assignable_type', UserGroup::class)->whereIn('assignable_id', $userGroupUuids))
                    ->orWhere(fn (Builder $q) => $q->where('assignable_type', Role::class)->whereIn('assignable_id', $roleUuids))
                    ->orWhere(fn (Builder $q) => $q->where('assignable_type', JobPosition::class)->whereIn('assignable_id', $jobPositionUuids))
                    ->orWhere(fn (Builder $q) => $q->where('assignable_type', Department::class)->whereIn('assignable_id', $departmentUuids));
            });
    }

    /**
     * Reachable Department/UserGroup uuids for a user: directly joined, or
     * via a Role/JobPosition the user holds that is attached to the container
     * (Role and JobPosition sit at the same level and both propagate
     * container grants to their members).
     *
     * @param class-string $containerClass
     * @param BelongsToMany $directRelation
     * @param Collection $roleUuids
     * @param Collection $jobPositionUuids
     * @return Collection
     */
    private function containerUuidsFor(string $containerClass, BelongsToMany $directRelation, Collection $roleUuids, Collection $jobPositionUuids): Collection
    {
        $directUuids = $directRelation->pluck($directRelation->getModel()->getTable().'.uuid');

        $viaRolesUuids = $containerClass::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('roles.uuid', $roleUuids))
            ->pluck('uuid');

        $viaJobPositionsUuids = $containerClass::query()
            ->whereHas('jobPositions', fn (Builder $query) => $query->whereIn('job_positions.uuid', $jobPositionUuids))
            ->pluck('uuid');

        return $directUuids->merge($viaRolesUuids)->merge($viaJobPositionsUuids)->unique();
    }
}
