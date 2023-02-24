<?php

declare(strict_types=1);

namespace Tests\Feature\Client;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Facades\Nostr;
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
    }

    public function test_nip19_encode_profile()
    {
        Http::fake(fn () => Http::response([
            'nprofile' => 'nprofile1',
        ]));

        $res = Nostr::nip19()->nprofile(profile: [
            'pubkey' => '1',
            'relays' => [],
        ]);

        $this->assertSame([
            'nprofile' => 'nprofile1',
        ], $res->json());
    }

    public function test_nip19_encode_event()
    {
        Http::fake(fn () => Http::response([
            'nevent' => 'nevent1',
        ]));

        $res = Nostr::nip19()->nevent(event: [
            'kind' => 0,
        ]);

        $this->assertSame([
            'nevent' => 'nevent1',
        ], $res->json());
    }

    public function test_nip19_encode_addr()
    {
        Http::fake(fn () => Http::response([
            'naddr' => 'naddr1',
        ]));

        $res = Nostr::nip19()->naddr(addr: [
            'identifier' => '1',
            'pubkey' => '11',
            'kind' => 0,
        ]);

        $this->assertSame([
            'naddr' => 'naddr1',
        ], $res->json());
    }
}
