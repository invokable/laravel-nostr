<?php

declare(strict_types=1);

namespace Tests\Feature;

use Revolution\Nostr\Event;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Tags\HashTag;
use Tests\TestCase;

class EventTest extends TestCase
{
    public function test_event()
    {
        $e = new Event(
            kind: Kind::Text,
            content: 'test',
            created_at: 0,
            tags: [['e', 'test']],
        );

        $e->withId(id: 'id')
          ->withPublicKey(pubkey: 'pub')
          ->withSign(sig: 'sig');

        $this->assertSame(json_encode([
            'id' => 'id',
            'pubkey' => 'pub',
            'sig' => 'sig',
            'kind' => 1,
            'content' => 'test',
            'created_at' => 0,
            'tags' => [['e', 'test']],
        ]), (string) $e);
    }

    public function test_to_array()
    {
        $e = new Event(
            kind: Kind::Text,
            tags: [HashTag::make(t: 'test')],
        );

        $this->assertSame([
            'kind' => 1,
            'content' => '',
            'created_at' => 0,
            'tags' => [['t', 'test']],
        ], $e->toArray());
    }

    public function test_make()
    {
        $e = Event::make(
            kind: Kind::Text,
            tags: [],
        )->withId('1');

        $this->assertSame([
            'id' => '1',
            'kind' => 1,
            'content' => '',
            'created_at' => 0,
            'tags' => [],
        ], $e->toArray());
    }

    public function test_make_signed()
    {
        $e = Event::makeSigned(
            kind: Kind::Text,
            content: '',
            created_at: 0,
            tags: [],
            id: '1',
            pubkey: '1',
            sig: '1',
        );

        $this->assertSame([
            'id' => '1',
            'pubkey' => '1',
            'sig' => '1',
            'kind' => 1,
            'content' => '',
            'created_at' => 0,
            'tags' => [],
        ], $e->toArray());
    }

    public function test_make_signed_from_array()
    {
        $e = Event::fromArray([
            'id' => '1',
            'pubkey' => '1',
            'sig' => '1',
            'kind' => 1,
            'content' => '',
            'created_at' => 0,
            'tags' => [],
        ]);

        $this->assertSame([
            'id' => '1',
            'pubkey' => '1',
            'sig' => '1',
            'kind' => 1,
            'content' => '',
            'created_at' => 0,
            'tags' => [],
        ], $e->toArray());
    }

    public function test_root_id()
    {
        $e = Event::make(
            tags: [['e', 'test', '', 'root']],
        );

        $this->assertSame('test', $e->rootId());
    }

    public function test_reply_id()
    {
        $e = Event::make(
            tags: [['e', 'test', '', 'reply']],
        );

        $this->assertSame('test', $e->replyId());
    }
}
