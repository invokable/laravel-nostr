<?php

declare(strict_types=1);

namespace Tests\Feature;

use Revolution\Nostr\Event;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Tag\HashTag;
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
}
