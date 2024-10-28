<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Client\Native\NativePool;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Filter;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Profile;
use Tests\TestCase;

class ClientPoolTest extends TestCase
{
    public function test_pool_event_publish()
    {
        Http::fakeSequence()
            ->whenEmpty(Http::response(['OK', 'subscription_id', true, '']));

        $event = new Event(kind: Kind::Text);

        $responses = (new NativePool())
            ->publish(event: $event, sk: '', relays: ['wss://1', 'wss://2']);

        $this->assertTrue($responses['wss://1']->ok());
        $this->assertTrue($responses['wss://2']->ok());
    }

    public function test_pool_publish_real()
    {
        $keys = Nostr::native()->key()->generate()->json();

        $profile = Profile::fromArray([
            'name' => 'test',
        ]);

        $event = Event::make(
            kind: Kind::Metadata,
            content: $profile->toJson(),
        );

        $responses = Nostr::native()
            ->pool()
            ->publish(
                event: $event,
                sk: $keys['sk'],
                relays: Arr::take(Config::get('nostr.relays'), limit: 2),
            );

        $response = head($responses);
        //dump($response->json());

        $this->assertIsArray($response->json());

        $id = $response->json('id');

        if (! empty($id)) {
            $filter = Filter::make(authors: [$keys['pk']], kinds: [Kind::Metadata]);

            $responses = Nostr::native()->pool()
                ->get(filter: $filter);

            $response = head($responses);

            //dump($response->json());

            $this->assertSame($id, $response->json('event.id'));

            $this->assertSame($profile->name, Profile::fromJson($response->json('event.content'))->name);
        }
    }

    public function test_pool_event_list()
    {
        Http::fakeSequence()
            ->whenEmpty(Http::response([]));

        $filter = new Filter(authors: []);

        $responses = (new NativePool())
            ->withRelays(relays: ['wss://1', 'wss://2'])
            ->list(filter: $filter);

        $this->assertTrue($responses['wss://1']->ok());
        $this->assertTrue($responses['wss://2']->ok());
    }

    public function test_pool_event_list_real()
    {
        $filter = new Filter(limit: 2);

        $responses = Nostr::driver('native')
            ->pool()
            ->list(
                filter: $filter,
                relays: Arr::take(Config::get('nostr.relays'), limit: 2),
            );

        $this->assertIsArray(head($responses)?->json());
        $this->assertCount(2, head($responses)?->json('events'));
        $this->assertCount(2, $responses);
    }

    public function test_pool_event_get()
    {
        Http::fakeSequence()
            ->whenEmpty(Http::response([]));

        $filter = new Filter(authors: []);

        $responses = (new NativePool())
            ->withRelays(relays: ['wss://1', 'wss://2'])
            ->get(filter: $filter, relays: ['1', '2']);

        $this->assertTrue($responses['1']->ok());
        $this->assertTrue($responses['2']->ok());
    }

    public function test_pool_event_get_real()
    {
        $filter = new Filter(kinds: [0], limit: 1);

        $responses = Nostr::driver('native')
            ->pool()
            ->get(
                filter: $filter,
                relays: Arr::take(Config::get('nostr.relays'), limit: 2),
            );

        $this->assertIsArray(head($responses)?->json());
        $this->assertArrayHasKey('event', head($responses)?->json());
        $this->assertCount(2, $responses);
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
