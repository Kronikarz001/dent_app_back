<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(UserServiceProvider::class);
        $this->app->register(PatientServiceProvider::class);
        $this->app->register(JobPositionServiceProvider::class);
        $this->app->register(FileServiceProvider::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
    }
}
