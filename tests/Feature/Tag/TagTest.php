<?php

declare(strict_types=1);

namespace Tests\Feature\Tag;

use Revolution\Nostr\Kind;
use Revolution\Nostr\Tag\AddressTag;
use Revolution\Nostr\Tag\IdentifierTag;
use Revolution\Nostr\Tag\ReferenceTag;
use Tests\TestCase;

class TagTest extends TestCase
{
    public function test_addr()
    {
        $a = AddressTag::make(
            kind: Kind::Text,
            pubkey: 'pk',
            identifier: 'd',
            relay: 'wss://'
        );

        $this->assertIsArray($a->toArray());
        $this->assertSame(['a', '1|pk|d', 'wss://'], $a->toArray());
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
}
