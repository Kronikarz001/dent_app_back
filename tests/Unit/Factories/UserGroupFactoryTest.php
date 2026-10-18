<?php

namespace Tests\Unit\Factories;

use App\Models\UserGroup;
use Tests\Unit\UnitTestCase;

/**
 * Summary of UserGroupFactoryTest
 */
final class UserGroupFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUserGroupCreateByFactory(): void
    {
        $group = UserGroup::factory()->create(['name' => 'Grupa testowa']);

        $this->assertEquals('Grupa testowa', $group->name);
    }
}
