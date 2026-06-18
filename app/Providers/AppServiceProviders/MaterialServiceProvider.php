<?php

namespace App\Providers\AppServiceProviders;

use App\Repositories\MaterialRepository;
use App\Repositories\MaterialRepositoryInterface;
use App\Services\MaterialService;
use App\Services\MaterialServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of MaterialServiceProvider
 */
class MaterialServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(MaterialRepositoryInterface::class, MaterialRepository::class);
        $this->app->bind(MaterialServiceInterface::class, MaterialService::class);
    }
}
