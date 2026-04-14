<?php

namespace App\Providers;

use App\Repositories\JobPositionRepository;
use App\Repositories\JobPositionRepositoryInterface;
use App\Services\JobPositionService;
use App\Services\JobPositionServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of JobPositionServiceProvider
 */
class JobPositionServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(JobPositionRepositoryInterface::class, JobPositionRepository::class);
        $this->app->bind(JobPositionServiceInterface::class, JobPositionService::class);
    }
}
