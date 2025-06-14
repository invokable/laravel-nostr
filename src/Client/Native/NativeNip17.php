<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientNip17;
use swentel\nostr\Key\Key;
use swentel\nostr\Nip17\DirectMessage;
use swentel\nostr\Nip59\GiftWrapService;
use swentel\nostr\Sign\Sign;

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
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function sendDirectMessage(
        #[\SensitiveParameter] string $sk,
        string $pk,
        string $message,
        array $additionalTags = [],
        ?string $replyToId = null,
    ): Response {
        $key = Container::getInstance()->make(Key::class);
        $sign = Container::getInstance()->make(Sign::class);
        $giftWrapService = new GiftWrapService($key, $sign);
        $directMessage = new DirectMessage($giftWrapService, $key);

        $result = $directMessage->sendDirectMessage(
            $sk,
            $pk,
            $message,
            $additionalTags,
            $replyToId
        );

        return $this->response([
            'seal' => [
                'id' => $result['seal']->getId(),
                'pubkey' => $result['seal']->getPublicKey(),
                'created_at' => $result['seal']->getCreatedAt(),
                'kind' => $result['seal']->getKind(),
                'tags' => $result['seal']->getTags(),
                'content' => $result['seal']->getContent(),
                'sig' => $result['seal']->getSignature(),
            ],
            'receiver' => [
                'id' => $result['receiver']->getId(),
                'pubkey' => $result['receiver']->getPublicKey(),
                'created_at' => $result['receiver']->getCreatedAt(),
                'kind' => $result['receiver']->getKind(),
                'tags' => $result['receiver']->getTags(),
                'content' => $result['receiver']->getContent(),
                'sig' => $result['receiver']->getSignature(),
            ],
            'sender' => [
                'id' => $result['sender']->getId(),
                'pubkey' => $result['sender']->getPublicKey(),
                'created_at' => $result['sender']->getCreatedAt(),
                'kind' => $result['sender']->getKind(),
                'tags' => $result['sender']->getTags(),
                'content' => $result['sender']->getContent(),
                'sig' => $result['sender']->getSignature(),
            ],
        ]);
    }

    /**
     * Decrypt a private direct message from a gift wrap.
     *
     * @param  array|object  $giftWrap  the gift wrapped event to decrypt
     * @param  string  $sk  receiver's secret key
     * @param  bool  $verifyRecipient  whether to verify that the gift wrap is addressed to the receiver
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function decryptDirectMessage(
        array|object $giftWrap,
        #[\SensitiveParameter] string $sk,
        bool $verifyRecipient = true,
    ): Response {
        try {
            // Convert array to stdClass if needed for compatibility
            if (is_array($giftWrap)) {
                $giftWrap = (object) $giftWrap;
            }

            $decryptedMessage = DirectMessage::decryptDirectMessage(
                $giftWrap,
                $sk,
                $verifyRecipient
            );

            return $this->response($decryptedMessage);
        } catch (Exception $e) {
            return $this->response([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
