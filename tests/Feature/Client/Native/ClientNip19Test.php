<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Illuminate\Http\Client\Response;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Nip19\AddressPointer;
use Revolution\Nostr\Nip19\EventPointer;
use Revolution\Nostr\Nip19\ProfilePointer;
use Tests\TestCase;

class ClientNip19Test extends TestCase
{
    public function test_decode()
    {
        $response = Nostr::native()->nip19()->decode('npub1qe3e5wrvnsgpggtkytxteaqfprz0rgxr8c3l34kk3a9t7e2l3acslezefe');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->successful());

        // Assert new {type, data} format to match NodeNip19 specification
        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals('npub', $json['type']);
        $this->assertIsString($json['data']); // Should be hex string
    }

    public function test_decode_note()
    {
        $response = Nostr::native()->nip19()->decode('note1g0lxye59xnj7mhkc0mjnx9uuay6n0fls0fz6ecyjm6y32xv0dn2qgzxdxu');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->successful());

        // Assert new {type, data} format to match NodeNip19 specification
        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals('note', $json['type']);
        $this->assertIsString($json['data']); // Should be event_id hex string
    }

    public function test_note()
    {
        $response = Nostr::native()->nip19()->note('43fb0422457c1fadec68c5ad18378abb2c626d6b787790973e888d0998f6ced4');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->successful());

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('note', $json);
        $this->assertIsString($json['note']);
    }

    public function test_nprofile()
    {
        $profile = ProfilePointer::make('06639a386c9c1014217622ccbcf40908c4f1a0c33e23f8d6d68f4abf655f8f71', ['wss://relay.example.com']);

        $response = Nostr::native()->nip19()->nprofile($profile);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->successful());

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('nprofile', $json);
        $this->assertIsString($json['nprofile']);
    }

    public function test_nevent()
    {
        $event = EventPointer::make('43fb0422457c1fadec68c5ad18378abb2c626d6b787790973e888d0998f6ced4', ['wss://relay.example.com'], '06639a386c9c1014217622ccbcf40908c4f1a0c33e23f8d6d68f4abf655f8f71');

        $response = Nostr::native()->nip19()->nevent($event);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->successful());

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('nevent', $json);
        $this->assertIsString($json['nevent']);
    }

    public function test_naddr()
    {
        $addr = AddressPointer::make('test_identifier', '06639a386c9c1014217622ccbcf40908c4f1a0c33e23f8d6d68f4abf655f8f71', 30023, ['wss://relay.example.com']);

        $response = Nostr::native()->nip19()->naddr($addr);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->successful());

        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('naddr', $json);
        $this->assertIsString($json['naddr']);
    }
}
