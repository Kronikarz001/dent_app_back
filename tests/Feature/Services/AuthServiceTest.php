<?php

namespace Tests\Feature\Services;

use App\Exceptions\AuthenticationException;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuthServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Summary of AuthServiceTest
 */
class AuthServiceTest extends TestCase
{
    /**
     * @var AuthServiceInterface|Application|mixed|object
     */
    private AuthServiceInterface $service;

    protected const TOKENS_TABLE = 'personal_access_tokens';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AuthServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testLoginReturnsTokenOnValidCredentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $request = new LoginRequest;
        $request->merge(['email' => $user->email, 'password' => 'secret123']);

        $response = $this->service->login($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertArrayHasKey('token', $response->getData(true));
    }

    /**
     * @return void
     */
    public function testLoginThrowsExceptionOnInvalidCredentials(): void
    {
        $this->expectException(AuthenticationException::class);

        User::factory()->create(['email' => 'test@example.com', 'password' => bcrypt('correct')]);

        $request = new LoginRequest;
        $request->merge(['email' => 'test@example.com', 'password' => 'wrong']);

        $this->service->login($request);
    }

    /**
     * @return void
     */
    public function testLogoutDeletesCurrentAccessToken(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        Auth::setUser($user);
        $user->withAccessToken($token->accessToken);

        $this->service->logout();

        $this->assertDatabaseMissing(self::TOKENS_TABLE, ['id' => $token->accessToken->id]);
    }

    /**
     * @return void
     */
    public function testAuthenticateSetsUserFromValidToken(): void
    {
        $user = User::factory()->create();
        $plain = $user->createToken('test')->plainTextToken;
        $tokenValue = explode('|', $plain)[1];

        $this->service->authenticate($tokenValue);

        $this->assertSame($user->uuid, Auth::user()->uuid);
    }

    /**
     * @return void
     */
    public function testAuthenticateThrowsExceptionForInvalidToken(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->service->authenticate('invalid-token');
    }

    /**
     * @return void
     */
    public function testAuthenticateThrowsExceptionForNullToken(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->service->authenticate(null);
    }
}
