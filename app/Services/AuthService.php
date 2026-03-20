<?php

namespace App\Services;

use App\Exceptions\AuthenticationException;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\Cache\UserRouteCacheService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

readonly class AuthService implements AuthServiceInterface
{
    /**
     * @param UserServiceInterface $userService
     * @param UserRouteCacheService $userRouteCacheService
     */
    public function __construct(
        private UserServiceInterface  $userService,
        private UserRouteCacheService $userRouteCacheService
    )
    {
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {

        if (!Auth::attempt($request->all())) {
            return $this->unauthorized();
        }

        /** @var User $user */
        $user = Auth::user();

        return new JsonResponse(
            $this->makeTokenResponse($user)
        );
    }

    /**
     * @return void
     */
    public function logout(): void
    {
        $user =  Auth::user();
        $this->userRouteCacheService->delete($user);
        $user->currentAccessToken()->delete();
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function resetPassword(array $data): JsonResponse
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return new JsonResponse(['message' => __($status)]);
        }

        return new JsonResponse(['message' => __($status)], 422);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function forgotPassword(array $data): JsonResponse
    {
        $status = Password::sendResetLink($data);

        if ($status === Password::RESET_LINK_SENT) {
            return new JsonResponse(['message' => __($status)]);
        }

        return new JsonResponse(['message' => __($status)], 422);
    }

    /**
     * @param string|null $token
     * @return void
     */
    public function authenticate(?string $token): void
    {
        $user = $this->userService->getUserByToken(
            $token,
        );

        if (is_null($user)) {
            throw new AuthenticationException();
        }

        Auth::setUser($user);
    }

    /**
     * @param User $user
     * @return array
     */
    private function makeTokenResponse(User $user): array
    {
        if (!is_null($user->token)) {
            return [
                'token' => $user->token,
                'type' => 'API_TOKEN',
            ];
        }

        $plainText = $user->createToken('token')->plainTextToken;

        return [
            'token' => $plainText,
            'type' => 'BEARER',
        ];
    }

    /**
     * @return JsonResponse
     */
    private function unauthorized(): JsonResponse
    {
        return new JsonResponse([
            'message' => __('bad.credentials'),
        ], 401);
    }
}
