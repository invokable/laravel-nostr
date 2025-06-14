<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Container\Container;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientKey;
use swentel\nostr\Key\Key;

class NativeKey implements ClientKey
{
    use Conditionable;
    use HasHttp;
    use Macroable;

    protected Key $key;

    public function __construct()
    {
        $this->key = Container::getInstance()->make(Key::class);
    }

    /**
     * Generate new cryptographic keys.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     *
     * $response = Nostr::native()->key()->generate();
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'sk' => 'hex_secret_key...',
     * //     'pk' => 'hex_public_key...',
     * //     'nsec' => 'nsec1...',
     * //     'npub' => 'npub1...',
     * // ]
     * ```
     *
     * @return \Illuminate\Http\Client\Response JSON: {sk: string, pk: string, nsec: string, npub: string}
     */
    public function generate(): Response
    {
        $sk = str_pad($this->key->generatePrivateKey(), 64, '0', STR_PAD_LEFT);
        $pk = str_pad($this->key->getPublicKey($sk), 64, '0', STR_PAD_LEFT);
        $nsec = $this->key->convertPrivateKeyToBech32($sk);
        $npub = $this->key->convertPublicKeyToBech32($pk);

        return $this->response(compact('sk', 'pk', 'nsec', 'npub'));
    }

    /**
     * Convert secret key to all key formats.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     *
     * $response = Nostr::native()->key()->fromSecretKey('hex_secret_key...');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'sk' => 'hex_secret_key...',
     * //     'pk' => 'hex_public_key...',
     * //     'nsec' => 'nsec1...',
     * //     'npub' => 'npub1...',
     * // ]
     * ```
     *
     * @param  string  $sk  Secret key in hex format
     * @return \Illuminate\Http\Client\Response JSON: {sk: string, pk: string, nsec: string, npub: string}
     */
    public function fromSecretKey(#[\SensitiveParameter] string $sk): Response
    {
        $sk = str_pad($sk, 64, '0', STR_PAD_LEFT);
        $pk = str_pad($this->key->getPublicKey($sk), 64, '0', STR_PAD_LEFT);
        $nsec = $this->key->convertPrivateKeyToBech32($sk);
        $npub = $this->key->convertPublicKeyToBech32($pk);

        return $this->response(compact('sk', 'pk', 'nsec', 'npub'));
    }

    /**
     * Convert nsec (bech32 secret key) to all key formats.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     *
     * $response = Nostr::native()->key()->fromNsec('nsec1...');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'sk' => 'hex_secret_key...',
     * //     'pk' => 'hex_public_key...',
     * //     'nsec' => 'nsec1...',
     * //     'npub' => 'npub1...',
     * // ]
     * ```
     *
     * @param  string  $nsec  Secret key in bech32 format (nsec1...)
     * @return \Illuminate\Http\Client\Response JSON: {sk: string, pk: string, nsec: string, npub: string}
     */
    public function fromNsec(#[\SensitiveParameter] string $nsec): Response
    {
        $sk = str_pad($this->key->convertToHex($nsec), 64, '0', STR_PAD_LEFT);
        $pk = str_pad($this->key->getPublicKey($sk), 64, '0', STR_PAD_LEFT);
        $npub = $this->key->convertPublicKeyToBech32($pk);

        return $this->response(compact('sk', 'pk', 'nsec', 'npub'));
    }

    /**
     * Convert public key to bech32 format.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     *
     * $response = Nostr::native()->key()->fromPublicKey('hex_public_key...');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'pk' => 'hex_public_key...',
     * //     'npub' => 'npub1...',
     * // ]
     * ```
     *
     * @param  string  $pk  Public key in hex format
     * @return \Illuminate\Http\Client\Response JSON: {pk: string, npub: string}
     */
    public function fromPublicKey(string $pk): Response
    {
        $pk = str_pad($pk, 64, '0', STR_PAD_LEFT);
        $npub = $this->key->convertPublicKeyToBech32($pk);

        return $this->response(compact('pk', 'npub'));
    }

    /**
     * Convert npub (bech32 public key) to hex format.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     *
     * $response = Nostr::native()->key()->fromNpub('npub1...');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'pk' => 'hex_public_key...',
     * //     'npub' => 'npub1...',
     * // ]
     * ```
     *
     * @param  string  $npub  Public key in bech32 format (npub1...)
     * @return \Illuminate\Http\Client\Response JSON: {pk: string, npub: string}
     */
    public function fromNpub(string $npub): Response
    {
        $pk = str_pad($this->key->convertToHex($npub), 64, '0', STR_PAD_LEFT);

        return $this->response(compact('pk', 'npub'));
    }
}
