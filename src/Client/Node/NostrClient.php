<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Node;

use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\PendingNip05;
use Revolution\Nostr\Contracts\NostrDriver;

/**
 * Basic Nostr client.
 */
class NostrClient implements NostrDriver
{
    use Macroable;

    public function key(): PendingKey
    {
        return new PendingKey();
    }

    public function event(): PendingEvent
    {
        return new PendingEvent();
    }

    public function pool(): PendingPool
    {
        return new PendingPool();
    }

    public function nip04(): PendingNip04
    {
        return new PendingNip04();
    }

    public function nip05(): PendingNip05
    {
        return new PendingNip05();
    }

    public function nip19(): PendingNip19
    {
        return new PendingNip19();
    }
}
