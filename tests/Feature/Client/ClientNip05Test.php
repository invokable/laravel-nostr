<?php

declare(strict_types=1);

namespace Tests\Feature\Client;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ClientNip05Test extends TestCase
{
    public function test_nip05_profile()
    {
        Http::fake(fn () => Http::response([
            'names' => [
                'user' => 'pubkey',
            ],
            'relays' => [
                'pubkey' => [
                    'relay1',
                ],
            ],
        ]));

        $user = Nostr::nip05()->profile(user: 'user@example.com');

        $this->assertSame([
            'user' => 'user@example.com',
            'pubkey' => 'pubkey',
            'relays' => ['relay1'],
        ], $user);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://example.com/.well-known/nostr.json?name=user';
        });
    }

    public function test_nip05_root()
    {
        Http::fake(fn () => Http::response([
            'names' => [
                '_' => 'pubkey',
            ],
            'relays' => [
                'pubkey' => [
                    'relay1',
                ],
            ],
        ]));

        $user = Nostr::nip05()->profile(user: 'example.com');

        $this->assertSame([
            'user' => 'example.com',
            'pubkey' => 'pubkey',
            'relays' => ['relay1'],
        ], $user);
    }

    public function test_nip05_empty()
    {
        Http::fake();

        $this->expectException(\Exception::class);

        $user = Nostr::nip05()->profile(user: '');
    }
}
