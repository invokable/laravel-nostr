<?php

declare(strict_types=1);

namespace Tests\Feature\Tag;

use Revolution\Nostr\Kind;
use Revolution\Nostr\Tags\AddressTag;
use Revolution\Nostr\Tags\DescriptionTag;
use Revolution\Nostr\Tags\EmojiTag;
use Revolution\Nostr\Tags\ExpirationTag;
use Revolution\Nostr\Tags\IdentifierTag;
use Revolution\Nostr\Tags\IdentityTag;
use Revolution\Nostr\Tags\ImageTag;
use Revolution\Nostr\Tags\KindTag;
use Revolution\Nostr\Tags\NameTag;
use Revolution\Nostr\Tags\PublishedAtTag;
use Revolution\Nostr\Tags\ReferenceTag;
use Revolution\Nostr\Tags\RelayTag;
use Revolution\Nostr\Tags\SubjectTag;
use Revolution\Nostr\Tags\TitleTag;
use Tests\TestCase;

class TagTest extends TestCase
{
    public function test_addr()
    {
        $a = AddressTag::make(
            kind: Kind::Text,
            pubkey: 'pk',
            identifier: 'd',
            relay: 'wss://',
        );

        $this->assertIsArray($a->toArray());
        $this->assertSame(['a', '1:pk:d', 'wss://'], $a->toArray());
    }

    public function test_identifier()
    {
        $d = IdentifierTag::make(
            d: 'identifier',
        );

        $this->assertIsArray($d->toArray());
        $this->assertSame(['d', 'identifier'], $d->toArray());
    }

    public function test_reference()
    {
        $r = ReferenceTag::make(
            r: 'reference',
        );

        $this->assertIsArray($r->toArray());
        $this->assertSame(['r', 'reference'], $r->toArray());
    }

    public function test_subject()
    {
        $s = SubjectTag::make(
            subject: 'subject',
        );

        $this->assertIsArray($s->toArray());
        $this->assertSame(['subject', 'subject'], $s->toArray());
    }

    public function test_expiration()
    {
        $e = ExpirationTag::make(
            expiration: 0,
        );

        $this->assertIsArray($e->toArray());
        $this->assertSame(['expiration', '0'], $e->toArray());
    }

    public function test_identity()
    {
        $i = IdentityTag::make(
            username: 'github:user',
            proof: 'proof',
        )->with(['1', '2']);

        $this->assertSame(['i', 'github:user', 'proof', '1', '2'], $i->toArray());
    }

    public function test_title()
    {
        $t = TitleTag::make(
            title: 'title',
        );

        $this->assertIsArray($t->toArray());
        $this->assertSame(['title', 'title'], $t->toArray());
    }

    public function test_name()
    {
        $tag = NameTag::make(
            name: 'test',
        );

        $this->assertSame(['name', 'test'], $tag->toArray());
    }

    public function test_kind()
    {
        $tag = KindTag::make(
            kind: Kind::Text,
        );

        $this->assertSame(['k', '1'], $tag->toArray());
    }

    public function test_image()
    {
        $tag = ImageTag::make(
            url: 'https://',
        );

        $tag_size = ImageTag::make(
            url: 'https://',
            width: 10,
            height: 10,
        );

        $this->assertSame(['image', 'https://'], $tag->toArray());
        $this->assertSame(['image', 'https://', '10x10'], $tag_size->toArray());
    }

    public function test_description()
    {
        $tag = DescriptionTag::make(
            description: 'test',
        );

        $this->assertSame(['description', 'test'], $tag->toArray());
    }

    public function test_relay()
    {
        $tag = RelayTag::make(
            relay: 'test',
            maker: 'm',
        );

        $this->assertSame(['relay', 'test', 'm'], $tag->toArray());
    }

    public function test_published()
    {
        $tag = PublishedAtTag::make(
            published_at: 0,
        );

        $this->assertSame(['published_at', '0'], $tag->toArray());
    }

    public function test_emoji()
    {
        $tag = EmojiTag::make(
            shortcode: 'test',
            url: 'https://',
        );

        $this->assertSame(['emoji', 'test', 'https://'], $tag->toArray());
    }
}
