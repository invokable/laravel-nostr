<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

/**
 * Integration tests for NIP-17 functionality using real WebSocket connections.
 * These tests connect to actual Nostr relays and should be run with care.
 *
 * @group integration
 */
class ClientNip17IntegrationTest extends TestCase
{
    /**
     * Test NIP-17 functionality with real keys and real WebSocket connections.
     * This test creates real keys, tests encryption/decryption, and uses
     * actual WebSocket connections to Nostr relays.
     *
     * @group integration
     */
    public function test_nip17_real_keys_with_real_websocket()
    {
        // Generate real keys for sender and receiver
        $senderKeys = Nostr::driver('native')->key()->generate()->json();
        $receiverKeys = Nostr::driver('native')->key()->generate()->json();

        $senderSk = $senderKeys['sk'];
        $receiverSk = $receiverKeys['sk'];
        $receiverPk = $receiverKeys['pk'];

        $message = 'Hello from real keys test! '.time();

        // Send the direct message (uses real keys, real WebSocket)
        $sendResponse = Nostr::driver('native')->nip17()->sendDirectMessage(
            sk: $senderSk,
            pk: $receiverPk,
            message: $message
        );

        // Verify the message was processed successfully
        $this->assertInstanceOf(\Illuminate\Http\Client\Response::class, $sendResponse);
        $sendData = $sendResponse->json();

        $this->assertTrue($sendData['success'] ?? false, 'Failed to send direct message: '.($sendData['error'] ?? 'Unknown error'));
        $this->assertTrue($sendData['published'] ?? false, 'Message was not published to relays');

        // Verify response structure
        $this->assertArrayHasKey('seal', $sendData);
        $this->assertArrayHasKey('receiver_gift_wrap', $sendData);
        $this->assertArrayHasKey('sender_gift_wrap', $sendData);

        // Get the receiver's gift wrap for decryption
        $receiverGiftWrap = $sendData['receiver_gift_wrap'];

        // Decrypt the message (should work since we encrypted it correctly with real keys)
        $decryptResponse = Nostr::driver('native')
            ->nip17()
            ->decryptDirectMessage(
                giftWrap: $receiverGiftWrap,
                sk: $receiverSk
            );

        $this->assertInstanceOf(\Illuminate\Http\Client\Response::class, $decryptResponse);
        $decryptData = $decryptResponse->json();

        // Verify successful decryption
        $this->assertTrue($decryptData['success'] ?? false, 'Failed to decrypt message: '.($decryptData['error'] ?? 'Unknown error'));
        $this->assertEquals($message, $decryptData['content'], 'Decrypted message content does not match original');
        $this->assertEquals($senderKeys['pk'], $decryptData['sender'], 'Sender public key does not match');

        // Verify additional response data
        $this->assertArrayHasKey('created_at', $decryptData);
        $this->assertArrayHasKey('tags', $decryptData);
        $this->assertArrayHasKey('rumor', $decryptData);
        $this->assertArrayHasKey('seal', $decryptData);
        $this->assertArrayHasKey('gift_wrap', $decryptData);

        // Verify the rumor structure
        $rumor = $decryptData['rumor'];
        $this->assertEquals(14, $rumor['kind']); // Kind::PrivateDirectMessage
        $this->assertEquals($message, $rumor['content']);

        // Verify the rumor has proper p-tag for receiver
        $pTags = collect($rumor['tags'] ?? [])->filter(fn ($tag) => ($tag[0] ?? '') === 'p');
        $this->assertCount(1, $pTags, 'Rumor should have exactly one p-tag');
        $this->assertEquals($receiverPk, $pTags->first()[1] ?? '', 'p-tag should contain receiver public key');

        // Additional verification: Check that the seal was properly created
        $seal = $decryptData['seal'];
        $this->assertEquals(13, $seal['kind']); // Kind::Seal
        $this->assertEquals($senderKeys['pk'], $seal['pubkey'], 'Seal should be signed by sender');

        // Additional verification: Check that the gift wrap was properly created
        $giftWrap = $decryptData['gift_wrap'];
        $this->assertEquals(1059, $giftWrap['kind']); // Kind::GiftWrap
        $this->assertArrayHasKey('pubkey', $giftWrap);
        $this->assertArrayHasKey('sig', $giftWrap);
    }

    /**
     * Test that WebSocket connections work when network allows it.
     * This test will be skipped if WebSocket connections are blocked.
     */
    public function test_websocket_connection_when_available()
    {
        // Try to establish a simple WebSocket connection
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->ws('wss://relay.nostr.band', function ($ws) {
                return ['status' => 'connected'];
            });

            $this->assertTrue(true, 'WebSocket connection succeeded');
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Connection refused') ||
                str_contains($e->getMessage(), 'timeout') ||
                str_contains($e->getMessage(), 'network')) {
                $this->markTestSkipped('WebSocket connection blocked by network/firewall: '.$e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Test that messages with real keys work correctly between different users.
     * Uses real key generation and real WebSocket connections.
     */
    public function test_nip17_multiple_users_real_keys()
    {
        // Generate real keys for multiple users
        $alice = Nostr::driver('native')->key()->generate()->json();
        $bob = Nostr::driver('native')->key()->generate()->json();
        $charlie = Nostr::driver('native')->key()->generate()->json();

        // Alice sends message to Bob
        $aliceToBobMessage = 'Hi Bob, this is Alice! '.time();
        $aliceToBobResponse = Nostr::driver('native')->nip17()->sendDirectMessage(
            sk: $alice['sk'],
            pk: $bob['pk'],
            message: $aliceToBobMessage
        );

        $this->assertTrue($aliceToBobResponse->json('success'));

        // Bob sends message to Charlie
        $bobToCharlieMessage = 'Hey Charlie, Bob here! '.time();
        $bobToCharlieResponse = Nostr::driver('native')->nip17()->sendDirectMessage(
            sk: $bob['sk'],
            pk: $charlie['pk'],
            message: $bobToCharlieMessage
        );

        $this->assertTrue($bobToCharlieResponse->json('success'));

        // Verify Bob can decrypt Alice's message
        $bobDecryptAlice = Nostr::driver('native')
            ->nip17()
            ->decryptDirectMessage(
                giftWrap: $aliceToBobResponse->json('receiver_gift_wrap'),
                sk: $bob['sk']
            );

        $this->assertTrue($bobDecryptAlice->json('success'));
        $this->assertEquals($aliceToBobMessage, $bobDecryptAlice->json('content'));
        $this->assertEquals($alice['pk'], $bobDecryptAlice->json('sender'));

        // Verify Charlie can decrypt Bob's message
        $charlieDecryptBob = Nostr::driver('native')
            ->nip17()
            ->decryptDirectMessage(
                giftWrap: $bobToCharlieResponse->json('receiver_gift_wrap'),
                sk: $charlie['sk']
            );

        $this->assertTrue($charlieDecryptBob->json('success'));
        $this->assertEquals($bobToCharlieMessage, $charlieDecryptBob->json('content'));
        $this->assertEquals($bob['pk'], $charlieDecryptBob->json('sender'));

        // Verify Charlie CANNOT decrypt Alice's message to Bob
        $charlieCannotDecryptAlice = Nostr::driver('native')
            ->nip17()
            ->decryptDirectMessage(
                giftWrap: $aliceToBobResponse->json('receiver_gift_wrap'),
                sk: $charlie['sk']
            );

        $this->assertFalse($charlieCannotDecryptAlice->json('success'), 'Charlie should not be able to decrypt Alice\'s message to Bob');

        // Verify Alice CANNOT decrypt Bob's message to Charlie
        $aliceCannotDecryptBob = Nostr::driver('native')
            ->nip17()
            ->decryptDirectMessage(
                giftWrap: $bobToCharlieResponse->json('receiver_gift_wrap'),
                sk: $alice['sk']
            );

        $this->assertFalse($aliceCannotDecryptBob->json('success'), 'Alice should not be able to decrypt Bob\'s message to Charlie');
    }
}
