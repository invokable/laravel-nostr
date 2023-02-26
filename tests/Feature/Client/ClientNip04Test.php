<?php

declare(strict_types=1);

namespace Tests\Feature\Client;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Client\NostrClient;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ClientNip04Test extends TestCase
{
    public function test_nip04_encrypt()
    {
        if (! method_exists(NostrClient::class, 'nip04')) {
            $this->markTestSkipped('nip04 does not work yet');
        }

        Http::fake(fn () => Http::response(['encrypt' => 'encrypt text']));

        $res = Nostr::nip04()->encrypt(sk: 'sk', pk: 'pk', content: 'content');

        $this->assertSame(['encrypt' => 'encrypt text'], $res->json());

        Http::assertSent(fn (Request $request) => $request['sk'] === 'sk');
    }

    public function test_nip04_decrypt()
    {
        if (! method_exists(NostrClient::class, 'nip04')) {
            $this->markTestSkipped('nip04 does not work yet');
        }

        Http::fake(fn () => Http::response(['decrypt' => 'decrypt text']));

        $res = Nostr::nip04()->decrypt(sk: 'sk', pk: 'pk', content: 'content');

        $this->assertSame(['decrypt' => 'decrypt text'], $res->json());

        Http::assertSent(fn (Request $request) => $request['sk'] === 'sk');
    }
}
