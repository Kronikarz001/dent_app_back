<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\EmployeeScheduleRepository;
use App\Repositories\EmployeeScheduleRepositoryInterface;
use App\Services\EmployeeScheduleService;
use App\Services\EmployeeScheduleServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of EmployeeScheduleServiceProvider
 */
class EmployeeScheduleServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(EmployeeScheduleRepositoryInterface::class, EmployeeScheduleRepository::class);
        $this->app->bind(EmployeeScheduleServiceInterface::class, EmployeeScheduleService::class);
    }
}
