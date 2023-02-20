<?php

namespace Revolution\Nostr\Facades;

use Illuminate\Support\Facades\Facade;
use Revolution\Nostr\Client\NostrClient;
use Revolution\Nostr\Client\PendingEvent;
use Revolution\Nostr\Client\PendingKey;
use Revolution\Nostr\Client\PendingNip05;
use Revolution\Nostr\Client\PendingPool;

/**
 * @method static PendingKey key()
 * @method static PendingEvent event()
 * @method static PendingNip05 nip05()
 * @method static PendingPool pool()
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
