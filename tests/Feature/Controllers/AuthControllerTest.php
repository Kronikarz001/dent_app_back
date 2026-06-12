<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Summary of AuthControllerTest
 */
class AuthControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testLoginReturnSuccessResponse(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testLoginWithInvalidCredentialsReturnUnauthorizedResponse(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @return void
     */
    public function testLogoutReturnSuccessResponse(): void
    {
        $user = User::factory()->create();

        $response = $this->callApiWithLoggedUser($user)
            ->postJson(route('auth.logout'));

        $response->assertOk();
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testForgotPasswordReturnSuccessResponse(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('user.forgot_password', ['user' => $user->uuid]), [
                'email' => $user->email,
            ]);

        $response->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * @return void
     */
    public function testResetPasswordReturnNoContentResponse(): void
    {
        $user = User::factory()->create();
        $token = app('auth.password.broker')->createToken($user);

        $response = $this->callApiWithLoggedUser()
            ->patchJson(route('user.resetPassword', ['user' => $user->uuid]), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertNoContent();
    }
}
