<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\MessageGroupRepository;
use App\Repositories\MessageGroupRepositoryInterface;
use App\Repositories\MessageRepository;
use App\Repositories\MessageRepositoryInterface;
use App\Services\MessageService;
use App\Services\MessageServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of MessageServiceProvider
 */
class MessageServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(MessageGroupRepositoryInterface::class, MessageGroupRepository::class);
        $this->app->bind(MessageServiceInterface::class, MessageService::class);
    }
}
