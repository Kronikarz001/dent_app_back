<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\MedicineRepository;
use App\Repositories\MedicineRepositoryInterface;
use App\Services\MedicineService;
use App\Services\MedicineServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of MedicineServiceProvider
 */
class MedicineServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(MedicineRepositoryInterface::class, MedicineRepository::class);
        $this->app->bind(MedicineServiceInterface::class, MedicineService::class);
    }
}
