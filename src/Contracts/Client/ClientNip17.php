<?php

namespace Revolution\Nostr\Contracts\Client;

use Illuminate\Http\Client\Response;

interface ClientNip17
{
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
    ): Response;

    /**
     * Decrypt a private direct message from a gift wrap.
     *
     * @param  array|object  $giftWrap  the gift wrapped event to decrypt
     * @param  string  $sk  receiver's secret key
     * @param  bool  $verifyRecipient  whether to verify that the gift wrap is addressed to the receiver
     */
    public function decryptDirectMessage(
        array|object $giftWrap,
        #[\SensitiveParameter] string $sk,
        bool $verifyRecipient = true,
    ): Response;
}
