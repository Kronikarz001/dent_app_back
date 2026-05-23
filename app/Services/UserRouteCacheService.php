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
     * @param User $key
     * @param UserRouteDto $data
     * @return void
     */
    public function set(mixed $key, mixed $data): void
    {
        parent::set($key->uuid, $data);
    }

    /**
     * @param User $key
     * @return UserRouteDto
     */
    public function get(mixed $key): UserRouteDto
    {
        return parent::get($key->uuid);
    }

    /**
     * @param User $key
     * @return void
     */
    public function delete(mixed $key): void
    {
        parent::delete($key->uuid);
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function has(mixed $key): bool
    {
        return parent::has($key->uuid);
    }

    /**
     * @return string
     */
    protected function getModulePrefix(): string
    {
        return 'userRoutes';
    }

    /**
     * @return int|null
     */
    protected function getTtl(): ?int
    {
        return 3600;
    }
}
