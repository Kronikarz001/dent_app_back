<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\UserGroupRepository;
use App\Repositories\UserGroupRepositoryInterface;
use App\Services\UserGroupService;
use App\Services\UserGroupServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of UserGroupServiceProvider
 */
class UserGroupServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(UserGroupRepositoryInterface::class, UserGroupRepository::class);
        $this->app->bind(UserGroupServiceInterface::class, UserGroupService::class);
    }
}
