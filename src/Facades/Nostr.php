<?php

namespace Revolution\Nostr\Facades;

use Illuminate\Support\Facades\Facade;
use Revolution\Nostr\Client\Native\NativeClient;
use Revolution\Nostr\Client\Native\NativeNip05;
use Revolution\Nostr\Client\Native\NativeRelay;
use Revolution\Nostr\Client\Node\NodeClient;
use Revolution\Nostr\Client\Node\NodeNip04;
use Revolution\Nostr\Contracts\Client\ClientEvent;
use Revolution\Nostr\Contracts\Client\ClientKey;
use Revolution\Nostr\Contracts\Client\ClientNip19;
use Revolution\Nostr\Contracts\Client\ClientPool;
use Revolution\Nostr\NostrManager;

/**
 * Basic Nostr client.
 *
 * @method static static driver(string $driver)
 * @method static NodeClient node()
 * @method static NativeClient native()
 * @method static ClientKey key()
 * @method static ClientEvent event()
 * @method static ClientPool pool()
 * @method static NativeRelay relay()
 * @method static NodeNip04 nip04()
 * @method static NativeNip05 nip05()
 * @method static ClientNip19 nip19()
 *
 * @see NodeClient
 * @see NativeClient
 */
class Nostr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NostrManager::class;
    }
}
