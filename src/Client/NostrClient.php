<?php
declare(strict_types=1);

namespace Revolution\Nostr\Client;

use Illuminate\Support\Traits\Macroable;

/**
 * Basic Nostr client.
 */
class NostrClient
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

    public function nip05(): PendingNip05
    {
        return new PendingNip05();
    }
}
