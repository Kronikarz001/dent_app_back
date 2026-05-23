<?php

namespace App\Services;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;

/**
 * Summary of AuthServiceInterface
 */
interface AuthServiceInterface
{
    public function login(LoginRequest $request): JsonResponse;

    public function logout(): void;

    public function forgotPassword(array $data): JsonResponse;

    public function resetPassword(array $data): JsonResponse;

    public function authenticate(?string $token): void;
}
