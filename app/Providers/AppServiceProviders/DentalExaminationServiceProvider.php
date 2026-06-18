<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\DentalExaminationRepository;
use App\Repositories\DentalExaminationRepositoryInterface;
use App\Services\DentalExaminationService;
use App\Services\DentalExaminationServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of DentalExaminationServiceProvider
 */
class DentalExaminationServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(DentalExaminationRepositoryInterface::class, DentalExaminationRepository::class);
        $this->app->bind(DentalExaminationServiceInterface::class, DentalExaminationService::class);
    }
}
