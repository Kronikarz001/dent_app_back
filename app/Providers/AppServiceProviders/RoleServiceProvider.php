<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\RoleRepository;
use App\Repositories\RoleRepositoryInterface;
use App\Services\RoleService;
use App\Services\RoleServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of RoleServiceProvider
 */
class RoleServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
    }
}
