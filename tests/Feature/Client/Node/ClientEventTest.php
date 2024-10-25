<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Node;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use Tests\TestCase;

class ClientEventTest extends TestCase
{
    public function test_event_publish()
    {
        Http::fake(fn () => Http::response(['message' => 'ok']));

        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('node')
            ->event()
            ->withRelay(relay: '')
            ->publish(event: $event, sk: '');

        $this->assertSame([
            'message' => 'ok',
        ], $response->json());
    }

    public function test_event_list()
    {
        Http::fake(fn () => Http::response(['events' => []]));

        $filter = new Filter(authors: []);

        $response = Nostr::driver('node')
            ->event()->list(filter: $filter, relay: '');

        $this->assertSame([
            'events' => [],
        ], $response->json());
    }

    public function test_event_get()
    {
        Http::fake(fn () => Http::response(['event' => []]));

        $filter = new Filter(authors: []);

        $response = Nostr::driver('node')
            ->event()->get(filter: $filter, relay: '');

        $this->assertSame([
            'event' => [],
        ], $response->json());
    }

    public function test_event_hash()
    {
        Http::fake(fn () => Http::response(['hash' => 'hash']));

        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('node')
            ->event()->hash(event: $event);

        $this->assertSame([
            'hash' => 'hash',
        ], $response->json());
    }

    public function test_event_sign()
    {
        Http::fake(fn () => Http::response(['sign' => 'sign']));

        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('node')
            ->event()->sign(event: $event, sk: 'sk');

        $this->assertSame([
            'sign' => 'sign',
        ], $response->json());
    }

    public function test_event_verify()
    {
        Http::fake(fn () => Http::response(['verify' => true]));

        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('node')
            ->event()->verify(event: $event);

        $this->assertSame([
            'verify' => true,
        ], $response->json());
    }
}
