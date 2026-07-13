<?php

namespace Tests\Unit\Services;

use App\Repositories\NotificationPreferenceRepository;
use App\Repositories\NotificationRepository;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Summary of NotificationServiceTest
 */
class NotificationServiceTest extends TestCase
{
    private MockInterface $repository;

    private MockInterface $preferenceRepository;

    private NotificationService $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(NotificationRepository::class);
        $this->preferenceRepository = Mockery::mock(NotificationPreferenceRepository::class);
        $this->service = new NotificationService($this->repository, $this->preferenceRepository);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testGetAllNotificationsReturnsNotificationsAndUnreadCount(): void
    {
        $collection = new Collection;

        $this->repository->shouldReceive('getAllForUser')->once()->with('user-uuid', false, 15)->andReturn($collection);
        $this->repository->shouldReceive('getUnreadCount')->once()->with('user-uuid')->andReturn(3);

        $result = $this->service->getAllNotifications('user-uuid');

        $this->assertSame($collection, $result['notifications']);
        $this->assertSame(3, $result['unread_count']);
    }

    /**
     * @return void
     */
    public function testMarkAsReadReturnsRemainingUnreadCount(): void
    {
        $this->repository->shouldReceive('markAsRead')->once()->with('user-uuid', 'notification-uuid');
        $this->repository->shouldReceive('getUnreadCount')->once()->with('user-uuid')->andReturn(1);

        $result = $this->service->markAsRead('user-uuid', 'notification-uuid');

        $this->assertSame(1, $result);
    }
}
