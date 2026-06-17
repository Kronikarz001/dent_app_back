<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\PatientRepository;
use App\Repositories\PatientRepositoryInterface;
use App\Services\PatientService;
use App\Services\PatientServiceInterface;
use App\Services\PhoneNumberService;
use App\Services\PhoneNumberServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of PatientServiceProvider
 */
class PatientServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PatientRepositoryInterface::class, PatientRepository::class);
        $this->app->bind(PatientServiceInterface::class, PatientService::class);
        $this->app->bind(PhoneNumberServiceInterface::class, PhoneNumberService::class);
    }
}
