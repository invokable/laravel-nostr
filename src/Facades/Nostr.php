<?php

namespace Revolution\Nostr\Facades;

use Illuminate\Support\Facades\Facade;
use Revolution\Nostr\Client\NostrClient;
use Revolution\Nostr\Client\PendingEvent;
use Revolution\Nostr\Client\PendingKey;
use Revolution\Nostr\Client\PendingNip05;
use Revolution\Nostr\Client\PendingNip19;
use Revolution\Nostr\Client\PendingPool;

/**
 * Basic Nostr client.
 *
 * @method static PendingKey key()
 * @method static PendingEvent event()
 * @method static PendingPool pool()
 * @method static PendingNip05 nip05()
 * @method static PendingNip19 nip19()
 *
 * @see NostrClient
 */
class Nostr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NostrClient::class;
    }
}
