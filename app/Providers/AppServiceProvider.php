<?php

namespace App\Providers;

use App\Repositories\JobPositionRepository;
use App\Repositories\JobPositionRepositoryInterface;
use App\Repositories\PatientRepository;
use App\Repositories\PatientRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use App\Services\AuthServiceInterface;
use App\Services\ExportService;
use App\Services\ExportServiceInterface;
use App\Services\JobPositionService;
use App\Services\JobPositionServiceInterface;
use App\Services\PatientService;
use App\Services\PatientServiceInterface;
use App\Services\PhoneNumberService;
use App\Services\PhoneNumberServiceInterface;
use App\Services\UserService;
use App\Services\UserServiceInterface;
use Illuminate\Http\Resources\Json\JsonResource;
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
        $this->app->bind(ExportServiceInterface::class, ExportService::class);
        $this->app->bind(JobPositionRepositoryInterface::class, JobPositionRepository::class);
        $this->app->bind(JobPositionServiceInterface::class, JobPositionService::class);
        $this->app->bind(PatientRepositoryInterface::class, PatientRepository::class);
        $this->app->bind(PatientServiceInterface::class, PatientService::class);
        $this->app->bind(PhoneNumberServiceInterface::class, PhoneNumberService::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
    }
}
