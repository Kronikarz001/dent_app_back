<?php

namespace App\Http\Middlewares;

use App\Services\AuthServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Summary of AccessTokenMiddleware
 */
class AccessTokenMiddleware
{
    /**
     * @const string
     */
    private const API_TOKEN_KEY = 'X-API-TOKEN';

    /**
     * @const
     */
    private const API_KEY = 'X-API-KEY';

    /**
     * @param AuthServiceInterface $authService
     */
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->headers->get(self::API_TOKEN_KEY) ?? $request->headers->get(self::API_KEY);

        $this->authService->authenticate($token);

        return $next($request);
    }
}
