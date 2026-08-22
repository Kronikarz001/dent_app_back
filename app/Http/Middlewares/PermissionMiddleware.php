<?php

namespace App\Http\Middlewares;

use App\Enums\PermissionType;
use App\Exceptions\PermissionDeniedException;
use App\Models\PermissionRoute;
use App\Models\User;
use App\Services\PermissionServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Summary of PermissionMiddleware
 */
class PermissionMiddleware
{
    /**
     * Routes that must always stay reachable for any authenticated user
     * (self-service account actions that must never be lockout-able by
     * missing permissions), independent of the resource/permission system.
     *
     * @var string[]
     */
    private const ALWAYS_ALLOWED = [
        'auth.logout',
        'role.delegate',
        'department.delegate',
        'department.createRole',
        'user-group.createRole',
    ];

    /**
     * @param PermissionServiceInterface $permissionService
     */
    public function __construct(
        private readonly PermissionServiceInterface $permissionService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     *
     * @throws PermissionDeniedException
     */
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * @var User|null $user
         */
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if ($user->is_admin) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (in_array($routeName, self::ALWAYS_ALLOWED, true)) {
            return $next($request);
        }

        $resource = PermissionRoute::query()
            ->where('route_name', $routeName)
            ->value('resource');

        if ($resource === null) {
            throw new PermissionDeniedException;
        }

        if (! $this->permissionService->hasPermission($user, $resource, PermissionType::fromRequest($request))) {
            throw new PermissionDeniedException;
        }

        return $next($request);
    }
}
