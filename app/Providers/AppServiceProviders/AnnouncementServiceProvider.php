<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\AnnouncementRepository;
use App\Repositories\AnnouncementRepositoryInterface;
use App\Services\AnnouncementService;
use App\Services\AnnouncementServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of AnnouncementServiceProvider
 */
class AnnouncementServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(AnnouncementRepositoryInterface::class, AnnouncementRepository::class);
        $this->app->bind(AnnouncementServiceInterface::class, AnnouncementService::class);
    }
}
