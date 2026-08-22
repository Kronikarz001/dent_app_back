<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\DepartmentRepository;
use App\Repositories\DepartmentRepositoryInterface;
use App\Services\DepartmentService;
use App\Services\DepartmentServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of DepartmentServiceProvider
 */
class DepartmentServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(DepartmentServiceInterface::class, DepartmentService::class);
    }
}
