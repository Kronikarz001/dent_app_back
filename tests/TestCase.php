<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Summary of TestCase
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @param User|null $user
     * @return $this
     */
    protected function callApiWithLoggedUser(?User $user = null): static
    {
        $user  = $user ?? User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token);

        return $this;
    }
}
