<?php

declare(strict_types=1);

namespace Tests\Feature\Client;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ClientKeyTest extends TestCase
{
    public function test_key_generate()
    {
        Http::fake(fn () => Http::response(['sk' => 'sk']));

        $response = Nostr::key()->generate();

        $this->assertSame([
            'sk' => 'sk',
        ], $response->json());
    }

    public function test_key_sk()
    {
        Http::fake(fn () => Http::response(['sk' => 'sk']));

        $response = Nostr::key()->fromSecretKey(sk: 'sk');

        $this->assertSame([
            'sk' => 'sk',
        ], $response->json());
    }

    public function test_key_nsec()
    {
        Http::fake(fn () => Http::response(['nsec' => 'nsec']));

        $response = Nostr::key()->fromNsec(nsec: 'nsec');

        $this->assertSame([
            'nsec' => 'nsec',
        ], $response->json());
    }

    public function test_key_pk()
    {
        Http::fake(fn () => Http::response(['pk' => 'pk']));

        $response = Nostr::key()->fromPublicKey(pk: 'pk');

        $this->assertSame([
            'pk' => 'pk',
        ], $response->json());
    }

    public function test_key_npub()
    {
        Http::fake(fn () => Http::response(['npub' => 'npub']));

        $response = Nostr::key()->fromNpub(npub: 'npub');

        $this->assertSame([
            'npub' => 'npub',
        ], $response->json());
    }
}
