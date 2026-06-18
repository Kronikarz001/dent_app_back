<?php

namespace Tests\Unit\Services;

use App\Dto\UserRouteDto;
use App\Models\User;
use App\Services\UserRouteCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Tests\Unit\UnitTestCase;

/**
 * Summary of UserRouteCacheServiceTest
 */
class UserRouteCacheServiceTest extends UnitTestCase
{
    private UserRouteCacheService $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UserRouteCacheService;
    }

    /**
     * @return void
     */
    public function testHasReturnsFalseWhenNothingCached(): void
    {
        $user = User::factory()->make(['uuid' => 'user-uuid-1']);

        $this->assertFalse($this->service->has($user));
    }

    /**
     * @return void
     */
    public function testSetThenGetReturnsStoredDto(): void
    {
        $user = User::factory()->make(['uuid' => 'user-uuid-2']);
        $dto = new UserRouteDto(collect(['a', 'b']));

        $this->service->set($user, $dto);

        $this->assertTrue($this->service->has($user));
        $this->assertEquals($dto, $this->service->get($user));
    }

    /**
     * @return void
     */
    public function testDeleteRemovesCachedValue(): void
    {
        $user = User::factory()->make(['uuid' => 'user-uuid-3']);
        $this->service->set($user, new UserRouteDto(collect()));

        $this->service->delete($user);

        $this->assertFalse($this->service->has($user));
    }

    /**
     * @return void
     */
    public function testDifferentUsersDoNotShareCacheEntries(): void
    {
        $userA = User::factory()->make(['uuid' => 'user-uuid-4']);
        $userB = User::factory()->make(['uuid' => 'user-uuid-5']);
        $dtoA = new UserRouteDto(collect(['only-for-a']));

        $this->service->set($userA, $dtoA);

        $this->assertTrue($this->service->has($userA));
        $this->assertFalse($this->service->has($userB));
    }

    /**
     * @return void
     */
    public function testValueIsStoredUnderPrefixedCacheKey(): void
    {
        $user = User::factory()->make(['uuid' => 'user-uuid-6']);
        $dto = new UserRouteDto(collect(['x']));

        $this->service->set($user, $dto);

        $this->assertTrue(Cache::has('userRoutes_user-uuid-6'));
        $this->assertEquals($dto, Cache::get('userRoutes_user-uuid-6'));
    }

    /**
     * @return void
     */
    public function testGetRoutesReturnsUnderlyingCollection(): void
    {
        $routes = collect(['route.one', 'route.two']);
        $dto = new UserRouteDto($routes);

        $this->assertInstanceOf(Collection::class, $dto->getRoutes());
        $this->assertSame($routes, $dto->getRoutes());
    }
}
