<?php

namespace Tests\Feature;

use Revolution\Nostr\Client\Node\PendingEvent;
use Revolution\Nostr\Client\Node\PendingKey;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    public function test_manager_instance()
    {
        $this->assertInstanceOf(PendingEvent::class, Nostr::driver('node')->event());
        $this->assertInstanceOf(PendingKey::class, Nostr::driver('node')->key());
    }
}
