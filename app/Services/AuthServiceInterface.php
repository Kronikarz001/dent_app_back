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
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse;

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
     * @param array $data
     * @return JsonResponse
     */
    public function resetPassword(array $data): JsonResponse;

    /**
     * @param string|null $token
     * @return void
     */
    public function authenticate(?string $token): void;
}
