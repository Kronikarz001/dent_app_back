<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
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

        $response = $this->postJson(route('user.forgot_password'), [
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

        $response = $this->postJson(route('user.reset_password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertNoContent();
    }

    /**
     * @return void
     */
    public function testEditPasswordReturnNoContentResponse(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

        $response = $this->callApiWithLoggedUser($user)
            ->patchJson(route('user.edit_password'), [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertNoContent();

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    /**
     * @return void
     */
    public function testEditPasswordWithWrongCurrentPasswordReturnsValidationError(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

        $response = $this->callApiWithLoggedUser($user)
            ->patchJson(route('user.edit_password'), [
                'current_password' => 'WrongPassword!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testEditPasswordRequiresAuthentication(): void
    {
        $response = $this->patchJson(route('user.edit_password'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertUnauthorized();
    }
}
