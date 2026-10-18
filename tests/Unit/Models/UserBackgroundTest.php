<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserBackground;
use Tests\Unit\UnitTestCase;

/**
 * Summary of UserBackgroundTest
 */
final class UserBackgroundTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testFindReturnsSameRowAsUserUnderDistinctMorphClass(): void
    {
        $user = User::factory()->create();

        $background = UserBackground::find($user->uuid);

        $this->assertNotNull($background);
        $this->assertSame($user->uuid, $background->uuid);
        $this->assertNotSame(User::class, UserBackground::class);
        $this->assertSame(UserBackground::class, $background->getMorphClass());
    }
}
