<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Summary of TestCase
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @return Application
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $app['config']->set('database.connections.pgsql.database', 'dent_db_back_test');

        return $app;
    }

    /**
     * @param User|null $user
     * @return $this
     */
    protected function callApiWithLoggedUser(?User $user = null): static
    {
        $user = $user ?? User::factory()->create(['is_admin' => true]);
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token);

        return $this;
    }
}
