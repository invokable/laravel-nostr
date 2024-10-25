<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Revolution\Nostr\Client\Native\DummyWebSocket;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use swentel\nostr\RelayResponse\RelayResponse;
use swentel\nostr\Sign\Sign;
use Tests\TestCase;

class ClientEventTest extends TestCase
{
    public function test_event_publish()
    {
        $this->mock(DummyWebSocket::class, function (MockInterface $mock) {
            $mock->shouldReceive('publish')->once()->andReturn([RelayResponse::create(['OK', 'subscription_id', true, 'message'])]);
        });

        $this->mock(Sign::class, function (MockInterface $mock) {
            $mock->shouldReceive('signEvent')->once();
        });

        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('native')->event()
            ->withRelay(relay: '')
            ->publish(event: $event, sk: '');

        $this->assertSame([
            'message' => 'ok',
            'id' => 'subscription_id',
        ], $response->json());
    }

//    public function test_event_publish_real()
//    {
//        $keys = Nostr::native()->key()->generate()->json();
//
//        $event = Event::make(
//            kind: Kind::Text,
//            content: 'test',
//        );
//
//        $response = Nostr::native()
//            ->event()
//            ->publish(event: $event, sk: $keys['sk']);
//
//        dump($response->json());
//
//        $this->assertIsArray($response->json());
//
//        $id = $response->json('id');
//        $filter = Filter::make(ids: [$id]);
//
//        $response = Nostr::native()->event()
//            ->get(filter: $filter);
//
//        dump($response->json());
//
//        $this->assertSame($id, $response->json('event.id'));
//    }

    public function test_event_list()
    {
        $this->mock(DummyWebSocket::class, function (MockInterface $mock) {
            $mock->shouldReceive('list')->once()->andReturn(
                new Response(Http::response([
                    'events' => [['id' => 'id']],
                ])->wait()),
            );
        });

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

        $this->assertIsArray($response->json());
        $this->assertTrue($response->successful());
        $this->assertCount(2, $response->json('events'));
    }

    public function test_event_get()
    {
        $this->mock(DummyWebSocket::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->once()->andReturn(
                new Response(Http::response([
                    'event' => ['id' => 'id'],
                ])->wait()),
            );
        });

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
        $this->mock(Sign::class, function (MockInterface $mock) {
            $mock->shouldReceive('serializeEvent')->once();
        });

        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('native')->event()->hash(event: $event);

        $this->assertArrayHasKey('hash', $response->json());
    }

    public function test_event_sign()
    {
        $this->mock(Sign::class, function (MockInterface $mock) {
            $mock->shouldReceive('signEvent')->once();
        });
        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('native')->event()->sign(event: $event, sk: 'sk');

        $this->assertArrayHasKey('sign', $response->json());
    }

    public function test_event_verify()
    {
        $event = new Event(kind: Kind::Text);

        $response = Nostr::driver('native')->event()->verify(event: $event);

        $this->assertSame([
            'verify' => false,
        ], $response->json());
    }
}
