<?php

namespace App\Providers\AppServiceProviders;

use App\Services\AuthService;
use App\Services\AuthServiceInterface;
use App\Services\ExportService;
use App\Services\ExportServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of AuthServiceProvider
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(ExportServiceInterface::class, ExportService::class);
    }
}
