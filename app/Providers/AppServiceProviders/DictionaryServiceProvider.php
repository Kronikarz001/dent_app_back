<?php

namespace App\Providers\AppServiceProviders;

use App\Services\DictionaryService;
use App\Services\DictionaryServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of DictionaryServiceProvider
 */
class DictionaryServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(DictionaryServiceInterface::class, DictionaryService::class);
    }
}
