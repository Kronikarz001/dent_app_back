<?php

namespace App\Providers;

use App\Repositories\FileRepository;
use App\Repositories\FileRepositoryInterface;
use App\Services\FileService;
use App\Services\FileServiceInterface;
use App\Services\UserAvatarService;
use App\Services\UserAvatarServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of FileServiceProvider
 */
class FileServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);

        $this->app->bind(FileServiceInterface::class, function ($app) {
            return new FileService(
                $app->make(FileRepositoryInterface::class),
                Storage::disk('local'),
            );
        });

        $this->app->bind(UserAvatarServiceInterface::class, function () {
            return new UserAvatarService(Storage::disk('local'));
        });
    }
}
