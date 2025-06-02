<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Str;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
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
            created_at: 1,
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
            'created_at' => 1,
            'tags' => [['e', 'test']],
        ]), (string) $e);
    }

    public function test_to_array()
    {
        $e = new Event(
            kind: Kind::Text,
            created_at: 1,
            tags: [HashTag::make(t: 'test')],
        );

        $this->assertSame([
            'kind' => 1,
            'content' => '',
            'created_at' => 1,
            'tags' => [['t', 'test']],
        ], $e->toArray());
    }

    public function test_make()
    {
        $e = Event::make(
            kind: Kind::Text,
            created_at: 1,
            tags: [],
        )->withId('1');

        $this->assertSame([
            'id' => '1',
            'kind' => 1,
            'content' => '',
            'created_at' => 1,
            'tags' => [],
        ], $e->toArray());
    }

    public function test_make_signed()
    {
        $e = Event::makeSigned(
            kind: Kind::Text,
            content: '',
            created_at: 1,
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
            'created_at' => 1,
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
            'created_at' => 1,
            'tags' => [],
        ]);

        $this->assertSame([
            'id' => '1',
            'pubkey' => '1',
            'sig' => '1',
            'kind' => 1,
            'content' => '',
            'created_at' => 1,
            'tags' => [],
        ], $e->toArray());
    }

    public function test_readonly()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot modify readonly property');

        $e = new Event;
        $e->kind = 0;
    }

    public function test_root_id()
    {
        $e = Event::make(
            tags: [['e', 'test', '', 'root'], ['e', 'test2', '', 'root']],
        );

        $this->assertSame('test', $e->rootId());

        $e = Event::make(
            tags: [['e', 'test']],
        );

        $this->assertNull($e->rootId());
    }

    public function test_reply_id()
    {
        $e = Event::make(
            tags: [['e', 'test', '', 'reply']],
        );

        $this->assertSame('test', $e->replyId());

        $e = Event::make(
            tags: [['e', 'test']],
        );

        $this->assertNull($e->replyId());
    }

    public function test_validate_unsigned()
    {
        $e = Event::make(
            kind: Kind::Text,
            content: 'test',
            created_at: 1,
            tags: [],
        );

        $this->assertTrue($e->validate());
    }

    public function test_validate_signed()
    {
        $e = Event::makeSigned(
            kind: Kind::Text,
            content: 'test',
            created_at: 1,
            tags: [],
            id: Str::random(64),
            pubkey: Str::random(64),
            sig: Str::random(128),
        );

        $this->assertTrue($e->validate());
    }

    public function test_hash()
    {
        $e = Event::make(
            kind: Kind::Text,
            content: 'い/ろ/は',
            created_at: 1,
            tags: [],
        )->withPublicKey('pk');

        $hash = $e->hash();

        $this->assertNotEmpty($hash);
        $this->assertSame('7b74a14ae5f1466977acbcff074b53f63023c14f4aa272970375bfecd91b2692', $hash);
    }

    public function test_hash_fail()
    {
        $this->expectException(\RuntimeException::class);

        $e = Event::make(
            kind: Kind::Text,
            content: 'い/ろ/は',
            created_at: 1,
            tags: [],
        );

        $hash = $e->hash();
    }

    public function test_isunsigned()
    {
        $e = Event::make();

        $this->assertTrue($e->isUnsigned());
    }

    public function test_sign()
    {
        $sk = Nostr::driver('native')->key()->generate()->json('sk');

        $e = Event::make()->sign($sk)->sign($sk);

        $this->assertTrue($e->isSigned());
        $this->assertTrue($e->validate());
    }
}
