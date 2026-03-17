<?php

namespace App\Services;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Summary of AuthService
 */
readonly class AuthService implements AuthServiceInterface
{
    /**
     * @param UserServiceInterface $userService
     */
    public function __construct(
        private UserServiceInterface $userService
    )
    {}

    /**
     * @param LoginRequest $request
     * @return User
     */
    public function login(LoginRequest $request): User
    {
        $user = $this->userService->findByEmail($request->email);

        if (!$user || !password_verify($request->password, $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        // Generate token logic here (e.g., JWT)
        // $token = $this->generateToken($user);

        return $user;
    }

    /**
     * @return void
     */
    public function logout(): void
    {

    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function forgotPassword(array $data): JsonResponse
    {

    }

    /**
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {

    }

    /**
     * @param string|null $token
     * @return void
     */
    public function authenticate(?string $token): void
    {

    }
}
