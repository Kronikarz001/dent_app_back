<?php

namespace App\Services;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Summary of AuthServiceInterface
 */
interface AuthServiceInterface
{
    /**
     * @param LoginRequest $request
     * @return User
     */
    public function login(LoginRequest $request): User;

    /**
     * @return void
     */
    public function logout(): void;

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
