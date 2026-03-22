<?php

namespace App\Providers;

use App\Repositories\JobPositionRepository;
use App\Repositories\JobPositionRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use App\Services\AuthServiceInterface;
use App\Services\JobPositionService;
use App\Services\JobPositionServiceInterface;
use App\Services\UserService;
use App\Services\UserServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of AppServiceProvider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(JobPositionRepositoryInterface::class, JobPositionRepository::class);
        $this->app->bind(JobPositionServiceInterface::class, JobPositionService::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
