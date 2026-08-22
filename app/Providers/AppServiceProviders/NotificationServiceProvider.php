<?php

namespace App\Providers\AppServiceProviders;

use App\Notifications\Channels\DatabaseChannel;
use App\Services\NotificationService;
use App\Services\NotificationServiceInterface;
use Illuminate\Notifications\ChannelManager;
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

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->callAfterResolving(ChannelManager::class, function (ChannelManager $manager) {
            $manager->extend('database', fn () => new DatabaseChannel);
        });
    }
}
