<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\PermissionRepository;
use App\Repositories\PermissionRepositoryInterface;
use App\Services\PermissionService;
use App\Services\PermissionServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of PermissionServiceProvider
 */
class PermissionServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
    }
}
