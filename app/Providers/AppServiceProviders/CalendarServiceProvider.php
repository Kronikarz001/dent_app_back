<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\CalendarRepository;
use App\Repositories\CalendarRepositoryInterface;
use App\Services\CalendarService;
use App\Services\CalendarServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of CalendarServiceProvider
 */
class CalendarServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(CalendarRepositoryInterface::class, CalendarRepository::class);
        $this->app->bind(CalendarServiceInterface::class, CalendarService::class);
    }
}
