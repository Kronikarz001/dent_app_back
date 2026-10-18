<?php

namespace Tests\Unit\Repositories;

use App\Models\Notification;
use App\Models\User;
use App\Repositories\NotificationRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Summary of NotificationRepositoryTest
 */
class NotificationRepositoryTest extends TestCase
{
    /**
     * @var NotificationRepository
     */
    private NotificationRepository $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(NotificationRepository::class);
    }

    /**
     * @param User $user
     * @param array $overrides
     * @return Notification
     */
    private function makeNotification(User $user, array $overrides = []): Notification
    {
        return Notification::create(array_merge([
            'type' => 'App\\Notifications\\SomeNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->uuid,
            'data' => [],
        ], $overrides));
    }

    /**
     * @return void
     */
    public function testGetAllForUserReturnsOnlyThatUsersNotifications(): void
    {
        $user = User::factory()->create();
        $own = $this->makeNotification($user);
        $this->makeNotification(User::factory()->create());

        $result = $this->repository->getAllForUser($user->uuid);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->contains($own));
        $this->assertCount(1, $result);
    }

    /**
     * @return void
     */
    public function testGetAllForUserWithPaginationReturnsPaginator(): void
    {
        $user = User::factory()->create();
        $this->makeNotification($user);

        $result = $this->repository->getAllForUser($user->uuid, true);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /**
     * @return void
     */
    public function testGetUnreadCountCountsOnlyUnreadNotifications(): void
    {
        $user = User::factory()->create();
        $this->makeNotification($user);
        $this->makeNotification($user, ['read_at' => now()]);

        $this->assertSame(1, $this->repository->getUnreadCount($user->uuid));
    }

    /**
     * @return void
     */
    public function testMarkAsReadWithoutUuidMarksAllNotificationsRead(): void
    {
        $user = User::factory()->create();
        $first = $this->makeNotification($user);
        $second = $this->makeNotification($user);

        $this->repository->markAsRead($user->uuid);

        $this->assertNotNull($first->refresh()->read_at);
        $this->assertNotNull($second->refresh()->read_at);
    }

    /**
     * @return void
     */
    public function testMarkAsReadWithUuidOnlyMarksThatNotification(): void
    {
        $user = User::factory()->create();
        $first = $this->makeNotification($user);
        $second = $this->makeNotification($user);

        $this->repository->markAsRead($user->uuid, $first->uuid);

        $this->assertNotNull($first->refresh()->read_at);
        $this->assertNull($second->refresh()->read_at);
    }
}
