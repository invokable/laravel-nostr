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
        $a = new AddressTag(
            kind: Kind::Text->value,
            pubkey: 'pk',
            identifier: 'd',
            relay: 'wss://'
        );

        $this->assertIsArray($a->toArray());
        $this->assertSame(['a', '1|pk|d', 'wss://'], $a->toArray());
    }

    public function test_identifier()
    {
        $d = new IdentifierTag(
            d: 'identifier',
        );

        $this->assertIsArray($d->toArray());
        $this->assertSame(['d', 'identifier'], $d->toArray());
    }

    public function test_reference()
    {
        $r = new ReferenceTag(
            r: 'reference',
        );

        $this->assertIsArray($r->toArray());
        $this->assertSame(['r', 'reference'], $r->toArray());
    }
}
