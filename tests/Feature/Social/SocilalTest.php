<?php

declare(strict_types=1);

namespace Tests\Feature\Social;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Exceptions\EventNotFoundException;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Facades\Social;
use Revolution\Nostr\Profile;
use Revolution\Nostr\Social\SocialClient;
use Revolution\Nostr\Tag\PersonTag;
use Tests\TestCase;

class SocilalTest extends TestCase
{
    protected SocialClient $social;

    public function setUp(): void
    {
        parent::setUp();

        $this->social = new SocialClient();
    }

    public function test_facade()
    {
        $social = Social::withRelay(relay: 'wss://')->withKey(sk: 'sk', pk: 'pk');

        $this->assertInstanceOf(SocialClient::class, $social);
    }

    public function test_create_new_user()
    {
        Nostr::shouldReceive('key->generate->collect')->once()->andReturn(collect([
            'sk' => 'sk',
            'pk' => 'pk',
        ]));

        Nostr::shouldReceive('event->publish->successful')->once()->andReturnTrue();

        $p = new Profile(name: 'name');

        $response = $this->social->createNewUser($p);

        $this->assertArrayHasKey('keys', $response);
        $this->assertArrayHasKey('profile', $response);
    }

    public function test_create_new_user_fail()
    {
        $this->expectException(\Exception::class);

        Nostr::shouldReceive('key->generate->collect')->once()->andReturn(collect());

        Nostr::shouldReceive('event->publish->successful')->never();

        $p = new Profile(name: 'name');

        $response = $this->social->withRelay('wss://')->createNewUser($p);

        $this->assertArrayNotHasKey('keys', $response);
        $this->assertArrayNotHasKey('profile', $response);
    }

    public function test_profile()
    {
        Nostr::shouldReceive('event->get->json')->once()->andReturn(['name' => 'name']);

        $response = $this->social->profile(pk: 'pk')->json();

        $this->assertIsArray($response);
    }

    public function test_follows()
    {
        Nostr::shouldReceive('event->get->collect')->once()->andReturn(collect([
            ['p', '1'],
            ['p', '2'],
        ]));

        $follows = $this->social->withKey('sk', 'pk')->follows();

        $this->assertSame(['1', '2'], $follows);
    }

    public function test_update_follows()
    {
        Nostr::shouldReceive('event->publish->successful')->once()->andReturnTrue();

        $follows = [
            new PersonTag(p: '1'),
            new PersonTag(p: '2'),
        ];

        $res = $this->social->withKey('sk', 'pk')
                            ->updateFollows(follows: $follows);

        $this->assertTrue($res->successful());
    }

    public function test_profiles()
    {
        Nostr::shouldReceive('event->list->json')->once()->andReturn([
            ['name' => '1'],
            ['name' => '2'],
        ]);

        $profiles = $this->social->profiles(['1', '2']);

        $this->assertIsArray($profiles);
    }

    public function test_notes()
    {
        Nostr::shouldReceive('event->list->collect->sortByDesc->toArray')->once()->andReturn([
            ['id' => '1'],
            ['id' => '2'],
        ]);

        $notes = $this->social->notes(authors: ['1', '2'], since: 0, until: 0, limit: 10);

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
        //follows
        Nostr::shouldReceive('event->get->collect')->once()->andReturn(collect([
            ['p', '1'],
            ['p', '2'],
        ]));

        //profiles
        Nostr::shouldReceive('event->list->json')->once()->andReturn([
            ['name' => '1', 'pubkey' => '1'],
            ['name' => '2', 'pubkey' => '2'],
        ]);

        //notes
        Nostr::shouldReceive('event->list->collect->sortByDesc->toArray')->once()->andReturn([
            ['id' => '1', 'pubkey' => '1'],
            ['id' => '2', 'pubkey' => '2'],
        ]);

        $notes = $this->social->withKey('sk', 'pk')
                              ->timeline(since: 0, until: 0, limit: 20);

        $this->assertIsArray($notes);
    }

    public function test_create_text_note()
    {
        Nostr::shouldReceive('event->publish->successful')->once()->andReturnTrue();

        $response = $this->social->withKey('sk', 'pk')
                                 ->createTextNote(content: 'test', tags: []);

        $this->assertTrue($response->successful());
    }

    public function test_create_text_note_to()
    {
        Nostr::shouldReceive('event->publish->successful')->once()->andReturnTrue();

        $response = $this->social->withKey('sk', 'pk')
                                 ->createTextNoteTo(content: 'test', pk: 'to');

        $this->assertTrue($response->successful());
    }

    public function test_create_text_note_hashtag()
    {
        Nostr::shouldReceive('event->publish->successful')->once()->andReturnTrue();

        $response = $this->social->withKey('sk', 'pk')
                                 ->createTextNoteWithHashTag(content: 'test', hashtags: ['test']);

        $this->assertTrue($response->successful());
    }

    public function test_reply()
    {
        Nostr::shouldReceive('event->publish->successful')->once()->andReturnTrue();

        $response = $this->social->withKey('sk', 'pk')
                                 ->reply(content: 'test', event_id: '1', to: ['1']);

        $this->assertTrue($response->successful());
    }

    public function test_delete()
    {
        Nostr::shouldReceive('event->publish->successful')->once()->andReturnTrue();

        $response = $this->social->withKey('sk', 'pk')
                                 ->delete(event_id: '1');

        $this->assertTrue($response->successful());
    }

    public function test_get_event_by_id()
    {
        Http::fake(fn () => Http::response([
            'event' => [
                'kind' => 1,
                'content' => '',
                'created_at' => 0,
                'tags' => [],
                'pubkey' => '1',
                'id' => '1',
                'sig' => '1',
            ],
        ]));

        $event = $this->social->withKey('sk', 'pk')
                              ->getEventById(id: '1');

        $this->assertSame([
            'id' => '1',
            'pubkey' => '1',
            'sig' => '1',
            'kind' => 1,
            'content' => '',
            'created_at' => 0,
            'tags' => [],
        ], $event->toArray());
    }

    public function test_get_event_by_id_http_failed()
    {
        Http::fake(fn () => Http::response('', 500));

        $this->expectException(RequestException::class);

        $event = $this->social->withKey('sk', 'pk')
                              ->getEventById(id: '1');
    }

    public function test_get_event_by_id_validator_fails()
    {
        Http::fake(fn () => Http::response(['event' => []]));

        $this->expectException(EventNotFoundException::class);

        $event = $this->social->withKey('sk', 'pk')
                              ->getEventById(id: '1');
    }
}
