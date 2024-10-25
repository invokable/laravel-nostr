<?php

namespace Revolution\Nostr\Facades;

use Illuminate\Support\Facades\Facade;
use Revolution\Nostr\Client\Native\NativeClient;
use Revolution\Nostr\Client\Native\NativeNip05;
use Revolution\Nostr\Client\Node\NodeClient;
use Revolution\Nostr\Client\Node\NodeNip04;
use Revolution\Nostr\Client\Node\NodeNip19;
use Revolution\Nostr\Contracts\Client\ClientEvent;
use Revolution\Nostr\Contracts\Client\ClientKey;
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
 * @method static NodeNip04 nip04()
 * @method static NativeNip05 nip05()
 * @method static NodeNip19 nip19()
 * @method static void fake(?callable $callback = null)
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
