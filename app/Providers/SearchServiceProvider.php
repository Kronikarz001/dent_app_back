<?php

namespace App\Providers;

use App\Repositories\SearchRepository;
use App\Repositories\SearchRepositoryInterface;
use App\Services\SearchService;
use App\Services\SearchServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Summary of SearchServiceProvider
 */
class SearchServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(SearchRepositoryInterface::class, SearchRepository::class);
        $this->app->bind(SearchServiceInterface::class, SearchService::class);
    }
}
