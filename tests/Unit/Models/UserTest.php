<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\Unit\UnitTestCase;

/**
 * Summary of UserTest
 */
final class UserTest extends UnitTestCase
{
    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function testGenderIsFemaleWhenPeselDigitIsEven(): void
    {
        $user = User::factory()->make(['pesel' => '90010112345']);

        $this->assertEquals('F', $user->gender);
    }

    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function testGenderIsMaleWhenPeselDigitIsOdd(): void
    {
        $user = User::factory()->make(['pesel' => '90010112335']);

        $this->assertEquals('M', $user->gender);
    }
}
