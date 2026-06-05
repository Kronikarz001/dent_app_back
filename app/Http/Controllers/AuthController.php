<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthServiceInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    #[OA\Post(
        path: '/api/auth/login',
        tags: ['Auth'],
        summary: 'Logowanie użytkownika',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token dostępu',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'token', type: 'string')]
                )
            ),
            new OA\Response(response: 401, description: 'Nieprawidłowe dane logowania'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->authService->login($request);
    }

    #[OA\Post(
        path: '/api/auth/logout',
        tags: ['Auth'],
        summary: 'Wylogowanie użytkownika',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Wylogowano pomyślnie'),
        ]
    )]
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return new JsonResponse;
    }

    #[OA\Post(
        path: '/api/auth/forgot-password',
        tags: ['Auth'],
        summary: 'Wysłanie linku do resetowania hasła',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Link wysłany'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->all());

        return new JsonResponse;
    }

    #[OA\Post(
        path: '/api/auth/reset-password',
        tags: ['Auth'],
        summary: 'Resetowanie hasła',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'token', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 204, description: 'Hasło zmienione'),
            new OA\Response(response: 422, description: 'Nieprawidłowy token lub dane'),
        ]
    )]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->all());

        return new JsonResponse(null, 204);
    }
}
