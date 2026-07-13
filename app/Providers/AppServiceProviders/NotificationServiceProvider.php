<?php

namespace App\Providers\AppServiceProviders;

use App\Services\NotificationService;
use App\Services\NotificationServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of NotificationServiceProvider
 */
class NotificationServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
    }
}
