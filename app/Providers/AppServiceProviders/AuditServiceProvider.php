<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\AuditRepository;
use App\Repositories\AuditRepositoryInterface;
use App\Services\AuditService;
use App\Services\AuditServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of AuditServiceProvider
 */
class AuditServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(AuditRepositoryInterface::class, AuditRepository::class);
        $this->app->bind(AuditServiceInterface::class, AuditService::class);
    }
}
