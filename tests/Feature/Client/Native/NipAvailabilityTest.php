<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class NipAvailabilityTest extends TestCase
{
    public function test_all_nip_methods_are_available_on_facade()
    {
        // Test that all NIP methods are available on their respective drivers
        $this->assertInstanceOf(\Revolution\Nostr\Client\Node\NodeNip04::class, Nostr::driver('node')->nip04());
        $this->assertInstanceOf(\Revolution\Nostr\Client\Native\NativeNip05::class, Nostr::driver('native')->nip05());
        $this->assertInstanceOf(\Revolution\Nostr\Client\Native\NativeNip17::class, Nostr::driver('native')->nip17());
        $this->assertInstanceOf(\Revolution\Nostr\Client\Native\NativeNip19::class, Nostr::driver('native')->nip19());

        // Test that the facade methods work with default driver (should be native)
        $this->assertInstanceOf(\Revolution\Nostr\Client\Native\NativeNip05::class, Nostr::nip05());
        $this->assertInstanceOf(\Revolution\Nostr\Contracts\Client\ClientNip17::class, Nostr::nip17());
        $this->assertInstanceOf(\Revolution\Nostr\Contracts\Client\ClientNip19::class, Nostr::nip19());
    }

    public function test_native_driver_nip04_throws_exception()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Native driver does not support nip04.');

        Nostr::driver('native')->nip04();
    }

    public function test_node_driver_nip17_throws_exception()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Node driver does not support nip17.');

        Nostr::driver('node')->nip17();
    }
}
