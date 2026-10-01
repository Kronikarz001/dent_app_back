<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\PrescriptionRepository;
use App\Repositories\PrescriptionRepositoryInterface;
use App\Services\PrescriptionService;
use App\Services\PrescriptionServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of PrescriptionServiceProvider
 */
class PrescriptionServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PrescriptionRepositoryInterface::class, PrescriptionRepository::class);
        $this->app->bind(PrescriptionServiceInterface::class, PrescriptionService::class);
    }
}
