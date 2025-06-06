<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Client\Native\NativePool;
use Revolution\Nostr\Client\Native\NativeWebSocket;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Profile;
use Tests\TestCase;

class ClientPoolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_pool_event_publish()
    {
        Http::fakeSequence()
            ->whenEmpty(Http::response(['OK', 'subscription_id', true, '']));

        $event = new Event(kind: Kind::Text);

        $responses = (new NativePool)
            ->publish(event: $event, sk: '', relays: ['wss://1', 'wss://2']);

        $this->assertTrue($responses['wss://1']->ok());
        $this->assertTrue($responses['wss://2']->ok());
    }

    public function test_pool_event_list()
    {
        Http::fakeSequence()
            ->whenEmpty(Http::response([]));

        $filter = new Filter(authors: []);

        $responses = (new NativePool)
            ->withRelays(relays: ['wss://1', 'wss://2'])
            ->list(filter: $filter);

        $this->assertTrue($responses['wss://1']->ok());
        $this->assertTrue($responses['wss://2']->ok());
    }

    public function test_pool_event_get()
    {
        Http::fakeSequence()
            ->whenEmpty(Http::response([]));

        $filter = new Filter(authors: []);

        $responses = (new NativePool)
            ->withRelays(relays: ['wss://1', 'wss://2'])
            ->get(filter: $filter, relays: ['1', '2']);

        $this->assertTrue($responses['1']->ok());
        $this->assertTrue($responses['2']->ok());
    }

    //    public function test_config_relays()
    //    {
    //        $responses = Http::pool(fn (Pool $pool) => collect(config('nostr.relays'))
    //            ->map(fn ($relay) => $pool->as($relay)->ws($relay, fn (NativeWebSocket $ws) => $ws->getWebSocket()->getMetadata()))->toArray());
    //
    //        dump($responses);
    //        $this->assertIsArray($responses);
    //    }
}
