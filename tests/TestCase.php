<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function callApiWithLoggedUser(?User $user = null): static
    {
        $user = $user ?? User::factory()->create();
        $this->actingAs($user);

        return $this;
    }
}
