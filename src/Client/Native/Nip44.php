<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Exception;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use swentel\nostr\Encryption\Nip44 as BaseNip44;

/**
 * NIP-44 encryption wrapper for this package.
 * Provides a clean interface to the nostr-php NIP-44 implementation.
 */
class Nip44
{
    use Conditionable;
    use Macroable;

    /**
     * Encrypt a message using NIP-44 encryption.
     *
     * @param  string  $plaintext  The message to encrypt
     * @param  string  $senderSk  The sender's secret key
     * @param  string  $receiverPk  The receiver's public key
     * @return string The encrypted message (base64 encoded)
     *
     * @throws Exception
     */
    public static function encrypt(string $plaintext, string $senderSk, string $receiverPk): string
    {
        $conversationKey = BaseNip44::getConversationKey($senderSk, $receiverPk);

        return BaseNip44::encrypt($plaintext, $conversationKey);
    }

    /**
     * Decrypt a message using NIP-44 encryption.
     *
     * @param  string  $ciphertext  The encrypted message (base64 encoded)
     * @param  string  $receiverSk  The receiver's secret key
     * @param  string  $senderPk  The sender's public key
     * @return string The decrypted message
     *
     * @throws Exception
     */
    public static function decrypt(string $ciphertext, string $receiverSk, string $senderPk): string
    {
        $conversationKey = BaseNip44::getConversationKey($receiverSk, $senderPk);

        return BaseNip44::decrypt($ciphertext, $conversationKey);
    }
}
