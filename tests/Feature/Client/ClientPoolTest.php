<?php

declare(strict_types=1);

namespace Tests\Feature\Client;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use Tests\TestCase;

class ClientPoolTest extends TestCase
{
    public function test_pool_event_publish()
    {
        Http::fake(fn () => Http::response(['message' => 'ok']));

        $event = new Event(kind: Kind::Text);

        $responses = Nostr::pool()
                          ->publish(event: $event, sk: '', relays: ['wss://1', 'wss://2']);

        $this->assertTrue($responses['wss://1']->ok());
        $this->assertTrue($responses['wss://2']->ok());
        Http::assertSentCount(2);
    }

    public function test_pool_event_list()
    {
        Http::fake(fn () => Http::response(['events' => []]));

        $filters = [
            new Filter(authors: []),
            new Filter(ids: []),
            [],
        ];

        $responses = Nostr::pool()
                          ->withRelays(relays: ['wss://1', 'wss://2'])
                          ->list(filters: $filters);

        $this->assertTrue($responses['wss://1']->ok());
        $this->assertTrue($responses['wss://2']->ok());
        Http::assertSentCount(2);
    }

    public function test_pool_event_get()
    {
        Http::fake(fn () => Http::response(['event' => []]));

        $filter = new Filter(authors: []);

        $responses = Nostr::pool()
                          ->withRelays(relays: ['wss://1', 'wss://2'])
                          ->get(filter: $filter, relays: ['1', '2']);

        $this->assertTrue($responses['1']->ok());
        $this->assertTrue($responses['2']->ok());
        Http::assertSentCount(2);
    }
}
