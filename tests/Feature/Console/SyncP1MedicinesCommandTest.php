<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Command;
use Tests\TestCase;

/**
 * Summary of SyncP1MedicinesCommandTest
 */
class SyncP1MedicinesCommandTest extends TestCase
{
    /**
     * @return void
     */
    public function testCommandFailsUntilP1IntegrationIsConnected(): void
    {
        $this->artisan('p1:sync-medicines')
            ->assertExitCode(Command::FAILURE);
    }
}
