<?php

namespace App\Dto;

use Illuminate\Support\Collection;

/**
 * Summary of UserRouteDto
 */
class UserRouteDto
{
    public function __construct(
        private Collection $routes,
    ) {}

    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function setRoutes(Collection $routes): void
    {
        $this->routes = $routes;
    }
}
