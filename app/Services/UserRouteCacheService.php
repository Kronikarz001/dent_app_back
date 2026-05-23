<?php

namespace App\Services;

use App\Dto\UserRouteDto;
use App\Models\User;

/**
 * @extends CacheServiceInterface<User, UserRouteDto>
 */
class UserRouteCacheService extends CacheService
{
    /**
     * @param  User  $key
     * @param  UserRouteDto  $data
     */
    public function set(mixed $key, mixed $data): void
    {
        parent::set($key->uuid, $data);
    }

    /**
     * @param  User  $key
     */
    public function get(mixed $key): UserRouteDto
    {
        return parent::get($key->uuid);
    }

    /**
     * @param  User  $key
     */
    public function delete(mixed $key): void
    {
        parent::delete($key->uuid);
    }

    public function has(mixed $key): bool
    {
        return parent::has($key->uuid);
    }

    protected function getModulePrefix(): string
    {
        return 'userRoutes';
    }

    protected function getTtl(): ?int
    {
        return 3600;
    }
}
