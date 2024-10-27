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
    public function test_event_publish()
    {
        Http::fakeSequence()
            ->push(['OK', 'subscription_id', true, '']);

        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('native')->event()
            ->withRelay(relay: '')
            ->publish(event: $event, sk: '', relay: 'wss://relay');

        //dump($response->json());

        $this->assertSame([
            'message' => 'OK',
            'id' => 'subscription_id',
        ], $response->json());
    }

    public function test_event_publish_real()
    {
        $keys = Nostr::native()->key()->generate()->json();

        $profile = Profile::fromArray([
            'name' => 'test',
        ]);

        $event = Event::make(
            kind: Kind::Metadata,
            content: $profile->toJson(),
        );

        $response = Nostr::native()
            ->event()
            ->publish(event: $event, sk: $keys['sk']);

        //dump($response->json());

        $this->assertIsArray($response->json());

        $id = $response->json('id');

        if (! empty($id)) {
            $filter = Filter::make(authors: [$keys['pk']], kinds: [Kind::Metadata]);

            $response = Nostr::native()->event()
                ->get(filter: $filter);

            //dump($response->json());

            $this->assertSame($id, $response->json('event.id'));

            $this->assertSame($profile->name, Profile::fromJson($response->json('event.content'))->name);
        }
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

    public function test_event_list_real()
    {
        $filter = new Filter(limit: 2);

        $response = Nostr::driver('native')->event()->list(filter: $filter, relay: 'wss://relay.nostr.band');

        //dump($response->json('events'));
        $this->assertIsArray($response->json());
        $this->assertTrue($response->successful());
        $this->assertCount(2, $response->json('events'));
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

    public function test_event_get_real()
    {
        $filter = new Filter(limit: 10);

        $response = Nostr::driver('native')->event()->get(filter: $filter, relay: 'wss://relay.nostr.band');

        $this->assertIsArray($response->json());
        $this->assertArrayHasKey('event', $response->json());
        $this->assertCount(1, $response->json());
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
