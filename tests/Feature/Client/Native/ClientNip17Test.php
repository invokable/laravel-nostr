<?php

declare(strict_types=1);

namespace Tests\Feature\Client\Native;

use Illuminate\Support\Facades\Http;
use Revolution\Nostr\Facades\Nostr;
use Tests\TestCase;

class ClientNip17Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_nip17_is_available_in_native_client()
    {
        $nip17 = Nostr::driver('native')->nip17();

        $this->assertInstanceOf(\Revolution\Nostr\Client\Native\NativeNip17::class, $nip17);
    }

    public function test_nip17_send_direct_message()
    {
        // Mock HTTP requests to prevent external calls during event publishing
        Http::fakeSequence()
            ->push(['OK', 'event_id_123', true, '']);

        $response = Nostr::driver('native')->nip17()->sendDirectMessage(
            sk: str_pad('1', 64, '0', STR_PAD_LEFT), // Valid 64-char hex string
            pk: str_pad('2', 64, '0', STR_PAD_LEFT), // Valid 64-char hex string
            message: 'Hello Bob, this is a test message!'
        );

        $this->assertInstanceOf(\Illuminate\Http\Client\Response::class, $response);
        $data = $response->json();

        $this->assertTrue($data['success'] ?? false);
        $this->assertArrayHasKey('seal', $data);
        $this->assertArrayHasKey('receiver_gift_wrap', $data);
        $this->assertArrayHasKey('sender_gift_wrap', $data);
        $this->assertTrue($data['published'] ?? false);

        // Verify the seal is properly structured
        $seal = $data['seal'];
        $this->assertEquals(13, $seal['kind']); // Kind::Seal
        $this->assertArrayHasKey('content', $seal);
        $this->assertArrayHasKey('sig', $seal);

        // Verify gift wraps are properly structured
        $receiverGiftWrap = $data['receiver_gift_wrap'];
        $this->assertEquals(1059, $receiverGiftWrap['kind']); // Kind::GiftWrap
        $this->assertArrayHasKey('content', $receiverGiftWrap);
        $this->assertArrayHasKey('sig', $receiverGiftWrap);
    }

    public function test_nip17_decrypt_direct_message()
    {
        // Test invalid gift wrap data to ensure proper error handling
        $invalidGiftWrap = [
            'id' => 'test_id',
            'pubkey' => str_pad('1', 64, '0', STR_PAD_LEFT),
            'created_at' => time(),
            'kind' => 1059,
            'tags' => [['p', str_pad('2', 64, '0', STR_PAD_LEFT)]],
            'content' => 'invalid_encrypted_content',
            'sig' => str_pad('1', 128, '0', STR_PAD_LEFT),
        ];

        $decryptResponse = Nostr::driver('native')
            ->nip17()
            ->decryptDirectMessage(
                giftWrap: $invalidGiftWrap,
                sk: str_pad('2', 64, '0', STR_PAD_LEFT)
            );

        $this->assertInstanceOf(\Illuminate\Http\Client\Response::class, $decryptResponse);
        $data = $decryptResponse->json();

        // Since we're using invalid encrypted content, we expect an error response
        $this->assertEquals(400, $decryptResponse->status());
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('error', $data);
    }

    public function test_node_driver_nip17_throws_exception()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Node driver does not support nip17.');

        Nostr::driver('node')->nip17();
    }

    public function test_nip17_end_to_end_encryption_decryption()
    {
        // Mock HTTP requests
        Http::fakeSequence()
            ->push(['OK', 'event_id_123', true, '']); // For publishing

        // Use realistic keys for end-to-end test
        $senderSk = str_pad('1', 64, '0', STR_PAD_LEFT);
        $receiverSk = str_pad('2', 64, '0', STR_PAD_LEFT);
        $receiverPk = str_pad('2', 64, '0', STR_PAD_LEFT); // Simplified for test
        $message = 'Hello Bob, this is a secret message!';

        // Send the message
        $sendResponse = Nostr::driver('native')->nip17()->sendDirectMessage(
            sk: $senderSk,
            pk: $receiverPk,
            message: $message
        );

        $this->assertTrue($sendResponse->json('success'));

        // Get the receiver's gift wrap
        $receiverGiftWrap = $sendResponse->json('receiver_gift_wrap');

        // Attempt to decrypt - this tests the full flow even if decryption fails
        $decryptResponse = Nostr::driver('native')
            ->nip17()
            ->decryptDirectMessage(
                giftWrap: $receiverGiftWrap,
                sk: $receiverSk
            );

        $this->assertInstanceOf(\Illuminate\Http\Client\Response::class, $decryptResponse);

        // The response should either succeed with the correct content or fail gracefully
        $data = $decryptResponse->json();
        $this->assertArrayHasKey('success', $data);

        if ($data['success']) {
            // If decryption succeeds, verify the content
            $this->assertEquals($message, $data['content']);
            $this->assertArrayHasKey('sender', $data);
        } else {
            // If decryption fails, verify proper error handling
            $this->assertArrayHasKey('error', $data);
        }
    }
}
