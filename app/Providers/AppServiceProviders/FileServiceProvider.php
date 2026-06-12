<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\FileRepository;
use App\Repositories\FileRepositoryInterface;
use App\Services\FileService;
use App\Services\FileServiceInterface;
use Illuminate\Support\ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(FileServiceInterface::class, FileService::class);
    }
}
