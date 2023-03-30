<?php

declare(strict_types=1);

namespace Tests\Feature\Client;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Nip19\AddressPointer;
use Revolution\Nostr\Nip19\EventPointer;
use Revolution\Nostr\Nip19\ProfilePointer;
use Tests\TestCase;

class ClientNip19Test extends TestCase
{
    public function test_nip19_decode()
    {
        Http::fake(fn () => Http::response([
            'type' => 'note',
            'data' => [],
        ]));

        $res = Nostr::nip19()->decode(n: 'note1aaaa');

        $this->assertSame([
            'type' => 'note',
            'data' => [],
        ], $res->json());
    }

    public function test_nip19_encode_note()
    {
        Http::fake(fn () => Http::response([
            'note' => 'note1aaa',
        ]));

        $res = Nostr::nip19()->note(id: '1');

        $this->assertSame([
            'note' => 'note1aaa',
        ], $res->json());

        Http::assertSent(fn (Request $request) => $request['note'] === '1');
    }

    public function test_nip19_encode_profile()
    {
        Http::fake(fn () => Http::response([
            'nprofile' => 'nprofile1',
        ]));

        $res = Nostr::nip19()->nprofile(profile: $p = ProfilePointer::make(
            pubkey: '1',
            relays: [],
        ));

        $this->assertSame([
            'nprofile' => 'nprofile1',
        ], $res->json());

        Http::assertSent(fn (Request $request) => $request['profile'] === $p->toArray());
    }

    public function test_nip19_encode_event()
    {
        Http::fake(fn () => Http::response([
            'nevent' => 'nevent1',
        ]));

        $res = Nostr::nip19()->nevent(event: $e = EventPointer::make(
            id: '1',
            relays: [],
            author: 'pk',
        ));

        $this->assertSame([
            'nevent' => 'nevent1',
        ], $res->json());

        Http::assertSent(fn (Request $request) => $request['event'] === $e->toArray());
    }

    public function test_nip19_encode_addr()
    {
        Http::fake(fn () => Http::response([
            'naddr' => 'naddr1',
        ]));

        $res = Nostr::nip19()->naddr(addr: $a = AddressPointer::make(
            identifier: '1',
            pubkey: '11',
            kind: Kind::Metadata,
            relays: [],
        ));

        $this->assertSame([
            'naddr' => 'naddr1',
        ], $res->json());

        Http::assertSent(fn (Request $request) => $request['addr'] === $a->toArray());
    }
}
