<?php

namespace App\Http\Controllers;

use App\Requests\ForgotPasswordRequest;
use App\Requests\LoginRequest;
use App\Requests\ResetPasswordRequest;
use App\Services\AuthServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * Summary of AuthController
 */
class AuthController extends Controller
{
    /**
     * @param AuthServiceInterface $authService
     */
    public function __construct(
        private readonly AuthServiceInterface $authService
    )
    {}

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->authService->login($request);
    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        return $this->authService->logout();
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->authService->forgotPassword($request->all());
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return $this->authService->resetPassword($request->all());
    }

}
