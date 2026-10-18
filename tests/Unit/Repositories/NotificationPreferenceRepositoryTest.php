<?php

namespace Tests\Unit\Repositories;

use App\Models\NotificationChannel;
use App\Models\NotificationGroup;
use App\Models\NotificationType;
use App\Models\User;
use App\Repositories\NotificationPreferenceRepository;
use Tests\TestCase;

/**
 * Summary of NotificationPreferenceRepositoryTest
 */
class NotificationPreferenceRepositoryTest extends TestCase
{
    /**
     * @var NotificationPreferenceRepository
     */
    private NotificationPreferenceRepository $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(NotificationPreferenceRepository::class);
    }

    /**
     * @return void
     */
    public function testUpdateOrCreatePersistsPreference(): void
    {
        $user = User::factory()->create();
        $type = NotificationType::create(['code' => 'test-pref-repo-type', 'display_name' => 'Test Type']);
        $channel = NotificationChannel::create(['name' => 'test-pref-repo-channel', 'display_name' => 'Test Channel']);

        $preference = $this->repository->updateOrCreate($user->uuid, $type->uuid, $channel->uuid, false);

        $this->assertFalse($preference->enabled);
        $this->assertDatabaseHas('notification_preferences', [
            'user_uuid' => $user->uuid,
            'notification_type_uuid' => $type->uuid,
            'notification_channel_uuid' => $channel->uuid,
            'enabled' => false,
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateOrCreateUpdatesExistingPreference(): void
    {
        $user = User::factory()->create();
        $type = NotificationType::create(['code' => 'test-pref-repo-type-2', 'display_name' => 'Test Type']);
        $channel = NotificationChannel::create(['name' => 'test-pref-repo-channel-2', 'display_name' => 'Test Channel']);
        $this->repository->updateOrCreate($user->uuid, $type->uuid, $channel->uuid, true);

        $this->repository->updateOrCreate($user->uuid, $type->uuid, $channel->uuid, false);

        $this->assertDatabaseCount('notification_preferences', 1);
        $this->assertDatabaseHas('notification_preferences', ['user_uuid' => $user->uuid, 'enabled' => false]);
    }

    /**
     * @return void
     */
    public function testGetUserPreferencesKeysByTypeAndChannel(): void
    {
        $user = User::factory()->create();
        $type = NotificationType::create(['code' => 'test-pref-repo-type-3', 'display_name' => 'Test Type']);
        $channel = NotificationChannel::create(['name' => 'test-pref-repo-channel-3', 'display_name' => 'Test Channel']);
        $this->repository->updateOrCreate($user->uuid, $type->uuid, $channel->uuid, true);

        $preferences = $this->repository->getUserPreferences($user->uuid);

        $this->assertTrue($preferences->has("{$type->uuid}:{$channel->uuid}"));
    }

    /**
     * @return void
     */
    public function testGetConfigurableChannelsExcludesInternalAndNonConfigurableChannels(): void
    {
        $configurable = NotificationChannel::create([
            'name' => 'test-pref-repo-configurable',
            'display_name' => 'Configurable',
            'is_configurable' => true,
            'is_internal' => false,
        ]);
        $internal = NotificationChannel::create([
            'name' => 'test-pref-repo-internal',
            'display_name' => 'Internal',
            'is_configurable' => true,
            'is_internal' => true,
        ]);

        $result = $this->repository->getConfigurableChannels();

        $this->assertTrue($result->contains($configurable));
        $this->assertFalse($result->contains($internal));
    }

    /**
     * @return void
     */
    public function testGetGroupsWithTypesOrdersByOrderAscending(): void
    {
        $second = NotificationGroup::create(['code' => 'test-pref-repo-group-2', 'display_name' => 'B', 'order' => 2]);
        $first = NotificationGroup::create(['code' => 'test-pref-repo-group-1', 'display_name' => 'A', 'order' => 1]);
        NotificationType::create([
            'code' => 'test-pref-repo-group-type',
            'display_name' => 'Test Type',
            'notification_group_uuid' => $first->uuid,
        ]);

        $groups = $this->repository->getGroupsWithTypes();
        $ownOrder = $groups->filter(fn (NotificationGroup $group) => in_array($group->uuid, [$first->uuid, $second->uuid], true))->values();

        $this->assertTrue($ownOrder->first()->is($first));
        $this->assertTrue($ownOrder->last()->is($second));
        $this->assertTrue($ownOrder->first()->notificationTypes->isNotEmpty());
    }
}
