<?php

namespace App\Providers;

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
        $this->app->bind(CalendarServiceInterface::class, CalendarService::class);
    }
}
