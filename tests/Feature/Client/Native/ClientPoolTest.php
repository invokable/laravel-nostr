<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Client\Native\NativePool;
use Revolution\Nostr\Event;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
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
}
