<?php

declare(strict_types=1);

namespace Tests\Feature\Social;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Revolution\Nostr\Event;
use Revolution\Nostr\Exceptions\EventNotFoundException;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Facades\Social;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Profile;
use Revolution\Nostr\Social\SocialClient;
use Revolution\Nostr\Tags\PersonTag;
use Tests\TestCase;

class NativeSocilalTest extends TestCase
{
    protected SocialClient $social;

    protected array $keys;

    protected function setUp(): void
    {
        parent::setUp();

        $this->keys = Nostr::native()->key()->generate()->json();

        $this->social = new SocialClient;
        $this->social->driver('native')->withRelay('wss://relay');
    }

    public function test_facade()
    {
        $social = Social::driver('native')
            ->withRelay(relay: 'wss://')
            ->withKey(sk: 'sk', pk: 'pk');

        $this->assertInstanceOf(SocialClient::class, $social);
    }

    public function test_create_new_user()
    {
        Nostr::shouldReceive('driver->key->generate->collect')->andReturn(collect($this->keys));

        Nostr::shouldReceive('driver->event->publish')->twice()->andReturn(new Response(Http::response()->wait()));

        $p = new Profile(name: 'name');

        $response = $this->social->createNewUser($p);

        $this->assertArrayHasKey('keys', $response);
        $this->assertArrayHasKey('profile', $response);
        $this->assertSame('name', data_get($response, 'profile.name'));
    }

    public function test_create_new_user_fail()
    {
        $this->expectException(\Exception::class);

        Nostr::shouldReceive('driver->key->generate->collect')->andReturn(collect([]));

        $p = new Profile(name: 'name');

        $response = $this->social->withRelay('wss://')->createNewUser($p);

        $this->assertArrayNotHasKey('keys', $response);
        $this->assertArrayNotHasKey('profile', $response);
    }

    public function test_profile()
    {
        Nostr::shouldReceive('driver->event->get->json')->andReturn(['name' => 'name']);

        $response = $this->social->profile(pk: 'pk');

        $this->assertIsArray($response);
        $this->assertSame('name', $response['name']);
    }

    public function test_follows()
    {
        Nostr::shouldReceive('driver->event->get->collect')->once()->andReturn(
            collect([
                ['p', '1'],
                ['p', '2'],
            ]),
        );

        $follows = $this->social->withKey('sk', 'pk')->follows();

        $this->assertSame(['1', '2'], $follows);
    }

    public function test_update_follows()
    {
        Nostr::shouldReceive('driver->event->publish')->once()->andReturn(
            new Response(Http::response()->wait()),
        );

        $follows = [
            new PersonTag(p: $this->keys['pk']),
            new PersonTag(p: $this->keys['pk']),
        ];

        $res = $this->social->withKey($this->keys['sk'], $this->keys['pk'])
            ->updateFollows(follows: $follows);

        $this->assertTrue($res->successful());
    }

    public function test_relays()
    {
        Nostr::shouldReceive('driver->event->get->json')->once()->andReturn(
            [
                ['r', 'wss://1'],
                ['r', 'wss://2'],
            ],
        );

        $follows = $this->social->withKey('sk', 'pk')->relays();

        $this->assertSame([['r', 'wss://1'], ['r', 'wss://2']], $follows);
    }

    public function test_update_relays()
    {
        Nostr::shouldReceive('driver->event->publish')->once()->andReturn(
            new Response(Http::response()->wait()),
        );

        $relays = [
            'wss://1',
            'wss://2',
        ];

        $res = $this->social->withKey($this->keys['sk'], $this->keys['pk'])
            ->updateRelays(relays: $relays);

        $this->assertTrue($res->successful());
    }

    public function test_profiles()
    {
        Nostr::shouldReceive('driver->event->list->json')->once()->andReturn([]);

        $profiles = $this->social->profiles([$this->keys['pk']]);

        $this->assertIsArray($profiles);
    }

    public function test_notes()
    {
        Nostr::shouldReceive('driver->event->list')->once()->andReturn(collect([]));

        $notes = $this->social->notes(authors: [$this->keys['pk']], kinds: [1], since: 0, until: 0, limit: 10);

        $this->assertIsArray($notes);
    }

    public function test_merge()
    {
        $notes = [
            [
                'pubkey' => '1',
                'content' => '1',
            ],
            [
                'pubkey' => '2',
                'content' => '2',
            ],
            [
                'content' => '3',
            ],
        ];

        $profiles = [
            [
                'pubkey' => '1',
                'content' => '{"name": "test"}',
            ],
            [
                'pubkey' => '2',
            ],
        ];

        $notes = $this->social->mergeNotesAndProfiles(notes: $notes, profiles: $profiles);

        $this->assertSame([
            [
                'pubkey' => '1',
                'content' => '1',
                'name' => 'test',
            ],
            [
                'pubkey' => '2',
                'content' => '2',
            ],
        ], $notes);
    }

    public function test_timeline()
    {
        Nostr::shouldReceive('driver->event->get->collect')->once()->andReturn(collect());
        Nostr::shouldReceive('driver->event->list->json')->once()->andReturn([]);
        Nostr::shouldReceive('driver->event->list->collect')->once()->andReturn(collect());

        $notes = $this->social->withKey('sk', 'pk')
            ->timeline(since: 0, until: 0, limit: 20);

        $this->assertIsArray($notes);
    }

    public function test_create_note()
    {
        Nostr::shouldReceive('driver->event->publish')->once()->andReturn(new Response(Http::response()->wait()));

        $response = $this->social->withKey('sk', 'pk')
            ->createNote(content: 'test', tags: []);

        $this->assertTrue($response->successful());
    }

    public function test_create_note_to()
    {
        Nostr::shouldReceive('driver->event->publish')->once()->andReturn(new Response(Http::response()->wait()));

        $response = $this->social->withKey('sk', 'pk')
            ->createNoteTo(content: 'test', pk: 'to');

        $this->assertTrue($response->successful());
    }

    public function test_create_note_hashtag()
    {
        Nostr::shouldReceive('driver->event->publish')->once()->andReturn(new Response(Http::response()->wait()));

        $response = $this->social->withKey('sk', 'pk')
            ->createNoteWithHashTag(content: 'test', hashtags: ['test']);

        $this->assertTrue($response->successful());
    }

    public function test_reply()
    {
        Nostr::shouldReceive('driver->event->publish')->once()->andReturn(new Response(Http::response()->wait()));

        $event = Event::makeSigned(
            kind: Kind::Text,
            content: 'test',
            created_at: 1,
            tags: [],
            id: '1',
            pubkey: '1',
            sig: '1',
        );

        $response = $this->social->withKey('sk', 'pk')
            ->reply(
                event: $event,
                content: 'test',
                mentions: ['1'],
                hashtags: ['test'],
            );

        $this->assertTrue($response->successful());
    }

    public function test_reply_root()
    {
        Nostr::shouldReceive('driver->event->publish')->once()->andReturn(new Response(Http::response()->wait()));

        $event = Event::makeSigned(
            kind: Kind::Text,
            content: 'test',
            created_at: 1,
            tags: [['e', '1', '', 'root']],
            id: '1',
            pubkey: '1',
            sig: '1',
        );

        $response = $this->social->withKey('sk', 'pk')
            ->reply(
                event: $event,
                content: 'test',
                mentions: ['1'],
            );

        $this->assertTrue($response->successful());
    }

    public function test_reaction()
    {
        Nostr::shouldReceive('driver->event->publish')->once()->andReturn(new Response(Http::response()->wait()));

        $event = Event::makeSigned(
            kind: Kind::Text,
            content: 'test',
            created_at: 1,
            tags: [],
            id: '1',
            pubkey: '1',
            sig: '1',
        );

        $response = $this->social->withKey('sk', 'pk')
            ->reaction(
                event: $event,
                content: '+',
            );

        $this->assertTrue($response->successful());
    }

    public function test_delete()
    {
        Nostr::shouldReceive('driver->event->publish')->once()->andReturn(new Response(Http::response()->wait()));

        $response = $this->social->withKey('sk', 'pk')
            ->delete(event_id: '1');

        $this->assertTrue($response->successful());
    }

    public function test_get_event_by_id()
    {
        $pk = Str::random(64);
        $id = Str::random(64);
        $sig = Str::random(128);

        Nostr::shouldReceive('driver->event->get->json')->once()->andReturn([
            'kind' => 1,
            'content' => '',
            'created_at' => 1,
            'tags' => [],
            'pubkey' => $pk,
            'id' => $id,
            'sig' => $sig,
        ]);

        $event = $this->social->withKey('sk', 'pk')
            ->getEventById(id: $id);

        $this->assertSame([
            'id' => $id,
            'pubkey' => $pk,
            'sig' => $sig,
            'kind' => 1,
            'content' => '',
            'created_at' => 1,
            'tags' => [],
        ], $event->toArray());
    }

    public function test_get_event_by_id_validator_fails()
    {
        Nostr::shouldReceive('driver->event->get->json')->once()->andReturn([
            'kind' => 1,
            'content' => '',
            'created_at' => 1,
            'tags' => [],
            'pubkey' => '',
            'id' => '',
            'sig' => '',
        ]);

        $this->expectException(EventNotFoundException::class);

        $event = $this->social->withKey('sk', 'pk')
            ->getEventById(id: '1');
    }
}
