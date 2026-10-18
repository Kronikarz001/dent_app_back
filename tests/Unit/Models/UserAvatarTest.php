<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserAvatar;
use Tests\Unit\UnitTestCase;

/**
 * Summary of UserAvatarTest
 */
final class UserAvatarTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testFindReturnsSameRowAsUserUnderDistinctMorphClass(): void
    {
        $user = User::factory()->create();

        $avatar = UserAvatar::find($user->uuid);

        $this->assertNotNull($avatar);
        $this->assertSame($user->uuid, $avatar->uuid);
        $this->assertNotSame(User::class, UserAvatar::class);
        $this->assertSame(UserAvatar::class, $avatar->getMorphClass());
    }
}
