<?php

namespace App\Providers\AppServiceProviders;

use App\Services\P1Service;
use App\Services\P1ServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of P1ServiceProvider
 */
class P1ServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(P1ServiceInterface::class, P1Service::class);
    }
}
