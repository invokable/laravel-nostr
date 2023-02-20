<?php
declare(strict_types=1);

namespace Tests\Feature\Client;

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

        $event = new Event(kind: Kind::Text->value);

        $response = Nostr::event()->publish(event: $event, sk: '', relay: '');

        $this->assertSame([
            'message' => 'ok',
        ], $response->json());
    }

    public function test_event_list()
    {
        Http::fake(fn () => Http::response(['events' => []]));

        $filters = [
            new Filter(authors: []),
            new Filter(ids: []),
            [],
        ];

        $response = Nostr::event()->list(filters: $filters, relay: '');

        $this->assertSame([
            'events' => [],
        ], $response->json());
    }

    public function test_event_get()
    {
        Http::fake(fn () => Http::response(['event' => []]));

        $filter = new Filter(authors: []);

        $response = Nostr::event()->get(filter: $filter, relay: '');

        $this->assertSame([
            'event' => [],
        ], $response->json());
    }

    public function test_event_hash()
    {
        Http::fake(fn () => Http::response(['hash' => 'hash']));

        $event = new Event(kind: Kind::Text->value);

        $response = Nostr::event()->hash(event: $event);

        $this->assertSame([
            'hash' => 'hash',
        ], $response->json());
    }

    public function test_event_sign()
    {
        Http::fake(fn () => Http::response(['sign' => 'sign']));

        $event = new Event(kind: Kind::Text->value);

        $response = Nostr::event()->sign(event: $event);

        $this->assertSame([
            'sign' => 'sign',
        ], $response->json());
    }
}
