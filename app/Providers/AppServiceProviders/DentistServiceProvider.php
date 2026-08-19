<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\DentistRepository;
use App\Repositories\DentistRepositoryInterface;
use App\Services\DentistService;
use App\Services\DentistServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of DentistServiceProvider
 */
class DentistServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(DentistRepositoryInterface::class, DentistRepository::class);
        $this->app->bind(DentistServiceInterface::class, DentistService::class);
    }
}
