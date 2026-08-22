<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\PermissionGroupRepository;
use App\Repositories\PermissionGroupRepositoryInterface;
use App\Services\PermissionGroupService;
use App\Services\PermissionGroupServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of PermissionGroupServiceProvider
 */
class PermissionGroupServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PermissionGroupRepositoryInterface::class, PermissionGroupRepository::class);
        $this->app->bind(PermissionGroupServiceInterface::class, PermissionGroupService::class);
    }
}
