<?php

namespace App\Services;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;

/**
 * Summary of AuthService
 */
class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserSe
    )
    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {

    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse;

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function forgotPassword(array $data): JsonResponse;

    /**
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse;

    /**
     * @param string|null $token
     * @return void
     */
    public function authenticate(?string $token): void;
}
