<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\PhotoRepository;
use App\Repositories\PhotoRepositoryInterface;
use App\Services\PhotoService;
use App\Services\PhotoServiceInterface;
use Illuminate\Support\ServiceProvider;

class PhotoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PhotoRepositoryInterface::class, PhotoRepository::class);
        $this->app->bind(PhotoServiceInterface::class, PhotoService::class);
    }
}
