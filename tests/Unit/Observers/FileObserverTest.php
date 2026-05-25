<?php

namespace Tests\Unit\Observers;

use App\Models\File;
use App\Models\User;
use App\Observers\FileObserver;
use Illuminate\Support\Facades\Auth;
use Tests\Unit\UnitTestCase;

/**
 * Summary of FileObserverTest
 */
class FileObserverTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function test_creating_sets_user_uuid_when_authenticated(): void
    {
        $user = User::factory()->make(['uuid' => 'test-user-uuid']);

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $file = new File;
        (new FileObserver)->creating($file);

        $this->assertSame('test-user-uuid', $file->user_uuid);
    }

    /**
     * @return void
     */
    public function test_creating_does_not_set_user_uuid_when_not_authenticated(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $file = new File;
        (new FileObserver)->creating($file);

        $this->assertNull($file->user_uuid);
    }
}
