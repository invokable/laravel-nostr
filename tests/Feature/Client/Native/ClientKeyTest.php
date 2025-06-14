<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Mockery\MockInterface;
use Revolution\Nostr\Facades\Nostr;
use swentel\nostr\Key\Key;
use Tests\TestCase;

class ClientKeyTest extends TestCase
{
    public function test_key()
    {
        $keys = Nostr::driver('native')->key()->generate()->json();

        $nsec = Nostr::driver('native')->key()->fromSecretKey($keys['sk'])->json('nsec');

        $sk = Nostr::driver('native')->key()->fromNsec($nsec)->json('sk');

        $this->assertSame($keys['nsec'], $nsec);
        $this->assertSame($keys['sk'], $sk);
    }

    public function test_key_generate()
    {
        $this->mock(Key::class, function (MockInterface $mock) {
            $mock->shouldReceive('generatePrivateKey')->once()->andReturn('sk');
            $mock->shouldReceive('getPublicKey')->once()->andReturn('pk');
            $mock->shouldReceive('convertPrivateKeyToBech32')->once()->andReturn('nsec');
            $mock->shouldReceive('convertPublicKeyToBech32')->once()->andReturn('npub');
        });

        $response = Nostr::driver('native')->key()->generate();

        $this->assertArrayHasKey('sk', $response->json());
        $this->assertArrayHasKey('nsec', $response->json());
        $this->assertArrayHasKey('pk', $response->json());
        $this->assertArrayHasKey('npub', $response->json());
        $this->assertSame([
            'sk' => str_pad('sk', 64, '0', STR_PAD_LEFT),
            'pk' => str_pad('pk', 64, '0', STR_PAD_LEFT),
            'nsec' => 'nsec',
            'npub' => 'npub',
        ], $response->json());
    }

    public function test_key_sk()
    {
        $this->mock(Key::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPublicKey')->once()->andReturn('pk');
            $mock->shouldReceive('convertPrivateKeyToBech32')->once()->andReturn('nsec');
            $mock->shouldReceive('convertPublicKeyToBech32')->once()->andReturn('npub');
        });

        $response = Nostr::driver('native')->key()->fromSecretKey(sk: 'sk');

        $this->assertArrayHasKey('sk', $response->json());
    }

    public function test_key_nsec()
    {
        $this->mock(Key::class, function (MockInterface $mock) {
            $mock->shouldReceive('convertToHex')->once()->andReturn('sk');
            $mock->shouldReceive('getPublicKey')->once()->andReturn('pk');
            $mock->shouldReceive('convertPublicKeyToBech32')->once()->andReturn('npub');
        });

        $response = Nostr::driver('native')->key()->fromNsec(nsec: 'nsec');

        $this->assertArrayHasKey('nsec', $response->json());
        $this->assertArrayHasKey('sk', $response->json());
        $this->assertArrayHasKey('pk', $response->json());
    }

    public function test_key_pk()
    {
        $this->mock(Key::class, function (MockInterface $mock) {
            $mock->shouldReceive('convertPublicKeyToBech32')->once()->andReturn('npub');
        });

        $response = Nostr::driver('native')->key()->fromPublicKey(pk: 'pk');

        $this->assertArrayNotHasKey('sk', $response->json());
        $this->assertArrayNotHasKey('nsec', $response->json());
        $this->assertArrayHasKey('npub', $response->json());
        $this->assertArrayHasKey('pk', $response->json());
    }

    public function test_key_npub()
    {
        $this->mock(Key::class, function (MockInterface $mock) {
            $mock->shouldReceive('convertToHex')->once()->andReturn('pk');
        });

        $response = Nostr::driver('native')->key()->fromNpub(npub: 'npub');

        $this->assertArrayNotHasKey('sk', $response->json());
        $this->assertArrayNotHasKey('nsec', $response->json());
        $this->assertArrayHasKey('npub', $response->json());
        $this->assertArrayHasKey('pk', $response->json());
    }
}
