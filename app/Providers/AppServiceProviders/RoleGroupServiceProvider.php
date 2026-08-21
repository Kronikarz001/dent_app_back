<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\RoleGroupRepository;
use App\Repositories\RoleGroupRepositoryInterface;
use App\Services\RoleGroupService;
use App\Services\RoleGroupServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of RoleGroupServiceProvider
 */
class RoleGroupServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(RoleGroupRepositoryInterface::class, RoleGroupRepository::class);
        $this->app->bind(RoleGroupServiceInterface::class, RoleGroupService::class);
    }
}
