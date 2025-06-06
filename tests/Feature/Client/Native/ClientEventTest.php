<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Profile;
use Tests\TestCase;

class ClientEventTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_event_publish()
    {
        Http::fakeSequence()
            ->push(['OK', 'subscription_id', true, '']);

        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('native')->event()
            ->withRelay(relay: '')
            ->publish(event: $event, sk: '', relay: 'wss://relay');

        // dump($response->json());

        $this->assertSame([
            'message' => 'OK',
            'id' => 'subscription_id',
        ], $response->json());
    }

    public function test_event_list()
    {
        Http::fakeSequence()
            ->push(['events' => [['id' => 'id']]]);

        $filter = new Filter(authors: []);

        $response = Nostr::driver('native')->event()->list(filter: $filter, relay: 'ws://relay');

        $this->assertSame([
            'events' => [['id' => 'id']],
        ], $response->json());
    }

    public function test_event_get()
    {
        Http::fakeSequence()
            ->push([
                'event' => ['id' => 'id'],
            ]);

        $filter = new Filter(authors: []);

        $response = Nostr::driver('native')->event()->get(filter: $filter, relay: 'ws://relay');

        $this->assertSame([
            'event' => ['id' => 'id'],
        ], $response->json());
    }

    public function test_event_hash()
    {
        $event = Event::make(kind: Kind::Text)->withPublicKey('pk');

        $response = Nostr::driver('native')->event()->hash(event: $event);

        $this->assertArrayHasKey('hash', $response->json());
    }

    public function test_event_sign()
    {
        $sk = Nostr::native()->key()->generate()->json('sk');

        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('native')->event()->sign(event: $event, sk: $sk);

        $this->assertArrayHasKey('sign', $response->json());
    }

    public function test_event_verify()
    {
        $sk = Nostr::native()->key()->generate()->json('sk');

        $event = Event::make(kind: Kind::Text)->sign($sk);

        $response = Nostr::driver('native')->event()->verify(event: $event);

        $this->assertSame([
            'verify' => true,
        ], $response->json());
    }
}
