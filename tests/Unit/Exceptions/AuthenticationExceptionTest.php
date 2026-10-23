<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Tests\Unit\UnitTestCase;

/**
 * Summary of AuthenticationExceptionTest
 */
final class AuthenticationExceptionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHasDefaultMessageAndUnauthorizedCode(): void
    {
        $exception = new AuthenticationException;

        $this->assertSame('Nieprawidłowy login lub hasło.', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }

    /**
     * @return void
     */
    public function testRenderReturnsUnauthorizedJson(): void
    {
        $response = (new AuthenticationException)->render(Request::create('/'));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(['message' => 'Nieprawidłowy login lub hasło.'], $response->getData(true));
    }

    /**
     * @return void
     */
    public function testCustomMessageKeepsUnauthorizedStatus(): void
    {
        $response = (new AuthenticationException('Konto zablokowane.'))->render(Request::create('/'));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(['message' => 'Konto zablokowane.'], $response->getData(true));
    }

    /**
     * @return void
     */
    public function testExtendsAuthorizationException(): void
    {
        $this->assertInstanceOf(AuthorizationException::class, new AuthenticationException);
    }
}
