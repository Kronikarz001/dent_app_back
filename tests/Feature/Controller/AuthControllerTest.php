<?php

namespace Tests\Feature\Controller;

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
    public function test_login_return_success_response(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
    }

    public function test_login_with_invalid_credentials_return_unauthorized_response(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_logout_return_success_response(): void
    {
        $user = User::factory()->create();

        $response = $this->callApiWithLoggedUser($user)
            ->postJson(route('auth.logout'));

        $response->assertOk();
    }

    /**
     * @throws Exception
     */
    public function test_forgot_password_return_success_response(): void
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

    public function test_reset_password_return_no_content_response(): void
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
