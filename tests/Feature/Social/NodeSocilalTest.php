<?php

declare(strict_types=1);

namespace Tests\Feature\Social;

use Illuminate\Http\Client\RequestException;
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

class NodeSocilalTest extends TestCase
{
    protected SocialClient $social;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->social = new SocialClient;
        $this->social->driver('node');
    }

    public function test_facade()
    {
        $social = Social::driver('node')
            ->withRelay(relay: 'wss://')
            ->withKey(sk: 'sk', pk: 'pk');

        $this->assertInstanceOf(SocialClient::class, $social);
    }

    public function test_create_new_user()
    {
        Http::fakeSequence()
            ->push([
                'sk' => 'sk',
                'pk' => 'pk',
            ])->whenEmpty(Http::response());

        $p = new Profile(name: 'name');

        $response = $this->social->createNewUser($p);

        $this->assertArrayHasKey('keys', $response);
        $this->assertArrayHasKey('profile', $response);
        $this->assertSame('name', data_get($response, 'profile.name'));
    }

    public function test_create_new_user_fail()
    {
        $this->expectException(\Exception::class);

        Http::fakeSequence()
            ->push([]);

        $p = new Profile(name: 'name');

        $response = $this->social->withRelay('wss://')->createNewUser($p);

        $this->assertArrayNotHasKey('keys', $response);
        $this->assertArrayNotHasKey('profile', $response);

        Http::assertNothingSent();
    }

    public function test_profile()
    {
        Http::fakeSequence()
            ->push(['event' => ['name' => 'name']]);

        $response = $this->social->profile(pk: 'pk');

        $this->assertIsArray($response);
        $this->assertSame('name', $response['name']);
    }

    public function test_follows()
    {
        Http::fakeSequence()
            ->push([
                'event' => [
                    'tags' => [
                        ['p', '1'],
                        ['p', '2'],
                    ],
                ]]);

        $follows = $this->social->withKey('sk', 'pk')->follows();

        $this->assertSame(['1', '2'], $follows);
    }

    public function test_update_follows()
    {
        Http::fake();

        $follows = [
            new PersonTag(p: '1'),
            new PersonTag(p: '2'),
        ];

        $res = $this->social->withKey('sk', 'pk')
            ->updateFollows(follows: $follows);

        $this->assertTrue($res->successful());
    }

    public function test_relays()
    {
        Http::fakeSequence()
            ->push([
                'event' => [
                    ['r', 'wss://1'],
                    ['r', 'wss://2'],
                ]]);

        $follows = $this->social->withKey('sk', 'pk')->relays();

        $this->assertSame([['r', 'wss://1'], ['r', 'wss://2']], $follows);
    }

    public function test_update_relays()
    {
        Http::fake();

        $relays = [
            'wss://1',
            'wss://2',
        ];

        $res = $this->social->withKey('sk', 'pk')
            ->updateRelays(relays: $relays);

        $this->assertTrue($res->successful());
    }

    public function test_profiles()
    {
        Http::fake();

        $profiles = $this->social->profiles(['1', '2']);

        $this->assertIsArray($profiles);
    }

    public function test_notes()
    {
        Nostr::shouldReceive('driver->event->list')->once()->andReturn(collect([]));

        $notes = $this->social->notes(authors: ['1', '2'], kinds: [1], since: 0, until: 0, limit: 10);

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
        Http::fake();

        $notes = $this->social->withKey('sk', 'pk')
            ->timeline(since: 0, until: 0, limit: 20);

        $this->assertIsArray($notes);
    }

    public function test_create_note()
    {
        Http::fake();

        $response = $this->social->withKey('sk', 'pk')
            ->createNote(content: 'test', tags: []);

        $this->assertTrue($response->successful());
    }

    public function test_create_note_to()
    {
        Http::fake();

        $response = $this->social->withKey('sk', 'pk')
            ->createNoteTo(content: 'test', pk: 'to');

        $this->assertTrue($response->successful());
    }

    public function test_create_note_hashtag()
    {
        Http::fake();

        $response = $this->social->withKey('sk', 'pk')
            ->createNoteWithHashTag(content: 'test', hashtags: ['test']);

        $this->assertTrue($response->successful());
    }

    public function test_reply()
    {
        Http::fake();

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
        Http::fake();

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
        Http::fake();

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
        Http::fake();

        $response = $this->social->withKey('sk', 'pk')
            ->delete(event_id: '1');

        $this->assertTrue($response->successful());
    }

    public function test_get_event_by_id()
    {
        $pk = Str::random(64);
        $id = Str::random(64);
        $sig = Str::random(128);

        Http::fake(fn () => Http::response([
            'event' => [
                'kind' => 1,
                'content' => '',
                'created_at' => 1,
                'tags' => [],
                'pubkey' => $pk,
                'id' => $id,
                'sig' => $sig,
            ],
        ]));

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

    public function test_get_event_by_id_type_error()
    {
        Http::fake(fn () => Http::response(['event' => []]));

        $this->expectException(\TypeError::class);

        $event = $this->social->withKey('sk', 'pk')
            ->getEventById(id: '1');
    }

    public function test_get_event_by_id_validator_fails()
    {
        Http::fake(fn () => Http::response([
            'event' => [
                'kind' => 1,
                'content' => '',
                'created_at' => 0,
                'tags' => [],
                'pubkey' => '',
                'id' => '',
                'sig' => '',
            ],
        ]));

        $this->expectException(EventNotFoundException::class);

        $event = $this->social->withKey('sk', 'pk')
            ->getEventById(id: '1');
    }
}
