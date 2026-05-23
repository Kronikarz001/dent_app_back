<?php

namespace Tests\Unit\Factory;

use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\Unit\UnitTestCase;

/**
 * Summary of UserFactoryTest
 */
final class UserFactoryTest extends UnitTestCase
{
    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function test_user_create_by_factory(): void
    {
        $user = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);

        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
    }
}
