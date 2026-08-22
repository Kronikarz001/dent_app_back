<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\CompanyRepository;
use App\Repositories\CompanyRepositoryInterface;
use App\Services\CompanyService;
use App\Services\CompanyServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of CompanyServiceProvider
 */
class CompanyServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(CompanyServiceInterface::class, CompanyService::class);
    }
}
