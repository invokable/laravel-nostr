<?php
declare(strict_types=1);

namespace Tests\Feature\Client;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ClientNip05Test extends TestCase
{
    public function test_nip05_profile()
    {
        Http::fake(fn () => Http::response(['pubkey' => 'pubkey']));

        $response = Nostr::nip05()->profile(user: 'user');

        $this->assertSame([
            'pubkey' => 'pubkey',
        ], $response->json());
    }
}
