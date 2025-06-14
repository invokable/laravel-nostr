<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ClientNip17Test extends TestCase
{
    public function test_nip17_is_available_in_native_client()
    {
        $nip17 = Nostr::driver('native')->nip17();

        $this->assertInstanceOf(\Revolution\Nostr\Client\Native\NativeNip17::class, $nip17);
    }

    public function test_nip17_send_direct_message()
    {
        // Generate valid test keys using the nostr Key class
        $key = new \swentel\nostr\Key\Key;

        $alicePrivKey = $key->generatePrivateKey();
        $alicePubKey = $key->getPublicKey($alicePrivKey);

        $bobPrivKey = $key->generatePrivateKey();
        $bobPubKey = $key->getPublicKey($bobPrivKey);

        $response = Nostr::driver('native')
            ->nip17()
            ->sendDirectMessage(
                sk: $alicePrivKey,
                pk: $bobPubKey,
                message: 'Hello Bob, this is a test message!'
            );

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertArrayHasKey('seal', $data);
        $this->assertArrayHasKey('receiver', $data);
        $this->assertArrayHasKey('sender', $data);

        // Verify the seal is kind 13
        $this->assertEquals(13, $data['seal']['kind']);
        // Verify the gift wraps are kind 1059
        $this->assertEquals(1059, $data['receiver']['kind']);
        $this->assertEquals(1059, $data['sender']['kind']);
    }

    public function test_nip17_decrypt_direct_message()
    {
        // This will test decryption by first creating a message and then decrypting it
        $key = new \swentel\nostr\Key\Key;

        $alicePrivKey = $key->generatePrivateKey();
        $alicePubKey = $key->getPublicKey($alicePrivKey);

        $bobPrivKey = $key->generatePrivateKey();
        $bobPubKey = $key->getPublicKey($bobPrivKey);

        $testMessage = 'Hello Bob, this is a test message!';

        // First, send a message
        $sendResponse = Nostr::driver('native')
            ->nip17()
            ->sendDirectMessage(
                sk: $alicePrivKey,
                pk: $bobPubKey,
                message: $testMessage
            );

        $this->assertTrue($sendResponse->successful());
        $sendData = $sendResponse->json();

        // Now decrypt the message using Bob's private key
        $decryptResponse = Nostr::driver('native')
            ->nip17()
            ->decryptDirectMessage(
                giftWrap: $sendData['receiver'],
                sk: $bobPrivKey
            );

        $this->assertTrue($decryptResponse->successful());
        $decryptedData = $decryptResponse->json();
        $this->assertEquals($testMessage, $decryptedData['content']);
        $this->assertEquals(14, $decryptedData['kind']); // Should be a kind 14 direct message
    }

    public function test_node_driver_nip17_throws_exception()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Node driver does not support nip17.');

        Nostr::driver('node')->nip17();
    }
}
