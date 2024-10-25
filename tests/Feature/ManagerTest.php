<?php

namespace Tests\Feature;

use Revolution\Nostr\Client\Native\NativeClient;
use Revolution\Nostr\Client\Native\NativeEvent;
use Revolution\Nostr\Client\Native\NativePool;
use Revolution\Nostr\Client\Node\NodeClient;
use Revolution\Nostr\Client\Node\NodeEvent;
use Revolution\Nostr\Client\Node\NodeKey;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    public function test_manager_instance()
    {
        $this->assertInstanceOf(NodeClient::class, Nostr::driver('node'));
        $this->assertInstanceOf(NodeClient::class, Nostr::node());
        $this->assertInstanceOf(NodeEvent::class, Nostr::driver('node')->event());
        $this->assertInstanceOf(NodeKey::class, Nostr::driver('node')->key());

        $this->assertInstanceOf(NativeClient::class, Nostr::driver('native'));
        $this->assertInstanceOf(NativeClient::class, Nostr::native());
        $this->assertInstanceOf(NativeEvent::class, Nostr::driver('native')->event());
        $this->assertInstanceOf(NativePool::class, Nostr::driver('native')->pool());
    }
}
