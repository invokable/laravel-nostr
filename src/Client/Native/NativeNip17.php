<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientNip17;
use Revolution\Nostr\Event;
use Revolution\Nostr\Facades\Nostr;
use Revolution\Nostr\Kind;
use Revolution\Nostr\Tags\PersonTag;
use swentel\nostr\Key\Key;

class NativeNip17 implements ClientNip17
{
    use Conditionable;
    use HasHttp;
    use Macroable;

    /**
     * Send a private direct message using NIP-17.
     *
     * @param  string  $sk  sender's secret key
     * @param  string  $pk  receiver's public key
     * @param  string  $message  message content
     * @param  array  $additionalTags  additional tags to include
     * @param  string|null  $replyToId  ID of message being replied to (optional)
     */
    public function sendDirectMessage(
        #[\SensitiveParameter] string $sk,
        string $pk,
        string $message,
        array $additionalTags = [],
        ?string $replyToId = null,
    ): Response {
        try {
            // Step 1: Create the rumor (kind 14) - unsigned direct message
            $rumor = $this->createRumor($message, $pk, $replyToId, $additionalTags);

            // Step 2: Create the seal (kind 13) - encrypt rumor with sender's key
            $seal = $this->createSeal($rumor, $sk, $pk);

            // Step 3: Create gift wraps (kind 1059) - encrypt seal with random keys
            $receiverGiftWrap = $this->createGiftWrap($seal, $pk);
            $senderGiftWrap = $this->createGiftWrap($seal, $this->getPublicKey($sk));

            // Publish the receiver's gift wrap to relays
            $publishResponse = Nostr::driver('native')
                ->event()
                ->publish($receiverGiftWrap, $this->generateRandomKey());

            return $this->response([
                'success' => true,
                'published' => $publishResponse->successful(),
                'seal' => $seal->toArray(),
                'receiver_gift_wrap' => $receiverGiftWrap->toArray(),
                'sender_gift_wrap' => $senderGiftWrap->toArray(),
            ]);
        } catch (Exception $e) {
            return $this->response([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Decrypt a private direct message from a gift wrap.
     *
     * @param  array  $giftWrap  the gift wrapped event to decrypt
     * @param  string  $sk  receiver's secret key
     * @param  bool  $verifyRecipient  whether to verify that the gift wrap is addressed to the receiver
     */
    public function decryptDirectMessage(
        array $giftWrap,
        #[\SensitiveParameter] string $sk,
        bool $verifyRecipient = true,
    ): Response {
        try {
            // Verify this is a gift wrap event
            if (($giftWrap['kind'] ?? null) !== Kind::GiftWrap->value) {
                throw new Exception('Invalid gift wrap: expected kind 1059');
            }

            // Step 1: Decrypt the gift wrap to get the seal
            $sealJson = Nip44::decrypt(
                $giftWrap['content'],
                $sk,
                $giftWrap['pubkey']
            );
            $sealData = json_decode($sealJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid seal JSON');
            }

            // Verify this is a seal event
            if (($sealData['kind'] ?? null) !== Kind::Seal->value) {
                throw new Exception('Invalid seal: expected kind 13');
            }

            // Step 2: Decrypt the seal to get the rumor
            $rumorJson = Nip44::decrypt(
                $sealData['content'],
                $sk,
                $sealData['pubkey']
            );
            $rumorData = json_decode($rumorJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid rumor JSON');
            }

            // Verify this is a direct message
            if (($rumorData['kind'] ?? null) !== Kind::PrivateDirectMessage->value) {
                throw new Exception('Invalid rumor: expected kind 14');
            }

            // Optional: Verify recipient
            if ($verifyRecipient) {
                $receiverPk = $this->getPublicKey($sk);
                $recipients = collect($rumorData['tags'] ?? [])
                    ->filter(fn ($tag) => ($tag[0] ?? '') === 'p')
                    ->pluck(1)
                    ->toArray();

                if (! in_array($receiverPk, $recipients)) {
                    throw new Exception('Message not addressed to this recipient');
                }
            }

            return $this->response([
                'success' => true,
                'content' => $rumorData['content'] ?? '',
                'sender' => $sealData['pubkey'] ?? '',
                'created_at' => $rumorData['created_at'] ?? 0,
                'tags' => $rumorData['tags'] ?? [],
                'rumor' => $rumorData,
                'seal' => $sealData,
                'gift_wrap' => $giftWrap,
            ]);
        } catch (Exception $e) {
            return $this->response([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a rumor (unsigned kind 14 event).
     */
    private function createRumor(
        string $message,
        string $receiverPk,
        ?string $replyToId = null,
        array $additionalTags = []
    ): Event {
        $tags = [
            PersonTag::make($receiverPk)->toArray(),
        ];

        if ($replyToId) {
            $tags[] = ['e', $replyToId];
        }

        foreach ($additionalTags as $tag) {
            $tags[] = $tag;
        }

        return Event::make(
            kind: Kind::PrivateDirectMessage,
            content: $message,
            tags: $tags
        );
    }

    /**
     * Create a seal (kind 13 event) that encrypts the rumor.
     */
    private function createSeal(Event $rumor, string $senderSk, string $receiverPk): Event
    {
        // Set the public key for the rumor (needed for JSON serialization)
        $senderPk = $this->getPublicKey($senderSk);
        $rumorWithPk = Event::make(
            kind: $rumor->kind,
            content: $rumor->content,
            created_at: $rumor->created_at,
            tags: $rumor->tags
        )->withPublicKey($senderPk);

        // Encrypt the rumor JSON
        $rumorJson = $rumorWithPk->toJson();
        $encryptedRumor = Nip44::encrypt($rumorJson, $senderSk, $receiverPk);

        // Create and sign the seal
        $seal = Event::make(
            kind: Kind::Seal,
            content: $encryptedRumor,
            tags: [] // Seals must have empty tags per NIP-59
        );

        return $seal->sign($senderSk);
    }

    /**
     * Create a gift wrap (kind 1059 event) that encrypts the seal.
     */
    private function createGiftWrap(Event $seal, string $recipientPk): Event
    {
        // Generate a random key for the gift wrap
        $randomSk = $this->generateRandomKey();

        // Encrypt the seal JSON
        $sealJson = $seal->toJson();
        $encryptedSeal = Nip44::encrypt($sealJson, $randomSk, $recipientPk);

        // Create and sign the gift wrap with random key
        $giftWrap = Event::make(
            kind: Kind::GiftWrap,
            content: $encryptedSeal,
            tags: [
                PersonTag::make($recipientPk)->toArray(),
            ]
        );

        return $giftWrap->sign($randomSk);
    }

    /**
     * Get public key from secret key.
     */
    private function getPublicKey(string $sk): string
    {
        return (new Key)->getPublicKey($sk);
    }

    /**
     * Generate a random private key.
     */
    private function generateRandomKey(): string
    {
        return (new Key)->generatePrivateKey();
    }
}
