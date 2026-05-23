<?php

namespace App\Dto;

use Illuminate\Support\Collection;

/**
 * Summary of UserRouteDto
 */
class UserRouteDto
{
    /**
     * @param Collection $routes
     */
    public function __construct(
        private Collection $routes,
    ) {}

    /**
     * @return Collection
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    /**
     * @param Collection $routes
     * @return void
     */
    public function setRoutes(Collection $routes): void
    {
        $this->routes = $routes;
    }
}
