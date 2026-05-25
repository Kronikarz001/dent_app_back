<?php

namespace Tests\Unit\Services;

use App\Exceptions\AuthenticationException;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Summary of AuthServiceTest
 */
class AuthServiceTest extends TestCase
{
    /**
     * @var AuthService|Application|mixed|object
     */
    private AuthService $authService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    /**
     * @return void
     */
    public function test_login_returns_user_on_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $request = LoginRequest::create('/login', 'POST', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $result = $this->authService->login($request);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->status());
    }

    /**
     * @return void
     */
    public function test_login_throws_exception_on_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->expectException(AuthenticationException::class);

        $this->authService->login($request);
    }

    /**
     * @return void
     */
    public function test_logout_deletes_current_access_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->actingAs($user, 'sanctum');

        $accessToken = $user->tokens()->first();
        $user->withAccessToken($accessToken);

        Auth::setUser($user);

        $this->authService->logout();

        $this->assertCount(0, $user->fresh()->tokens);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->authService->forgotPassword(['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * @return void
     */
    public function test_forgot_password_returns_success_response(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $result = $this->authService->forgotPassword(['email' => $user->email]);

        $this->assertEquals(200, $result->getStatusCode());
    }

    /**
     * @return void
     */
    public function test_forgot_password_returns_error_response_for_unknown_email(): void
    {
        $result = $this->authService->forgotPassword(['email' => 'nonexistent@example.com']);

        $this->assertEquals(422, $result->getStatusCode());
    }

    /**
     * @return void
     */
    public function test_reset_password_changes_user_password(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->authService->resetPassword([
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $this->assertTrue(password_verify('NewPassword123!', $user->fresh()->password));
    }

    /**
     * @return void
     */
    public function test_reset_password_returns_success_response(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $result = $this->authService->resetPassword([
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $this->assertEquals(200, $result->getStatusCode());
    }

    /**
     * @return void
     */
    public function test_reset_password_returns_error_response_for_invalid_token(): void
    {
        $user = User::factory()->create();

        $result = $this->authService->resetPassword([
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $this->assertEquals(422, $result->getStatusCode());
    }

    /**
     * @return void
     */
    public function test_authenticate_sets_user_from_valid_token(): void
    {
        $user = User::factory()->create();
        $plainToken = $user->createToken('test-token')->plainTextToken;

        $this->authService->authenticate($plainToken);

        $this->assertEquals($user->uuid, Auth::user()->uuid);
    }

    /**
     * @return void
     */
    public function test_authenticate_throws_exception_for_invalid_token(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->authService->authenticate('invalid-token');
    }

    /**
     * @return void
     */
    public function test_authenticate_throws_exception_for_null_token(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->authService->authenticate(null);
    }
}
