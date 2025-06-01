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

    public function generate(): Response
    {
        $sk = $this->key->generatePrivateKey();
        $pk = $this->key->getPublicKey($sk);
        $nsec = $this->key->convertPrivateKeyToBech32($sk);
        $npub = $this->key->convertPublicKeyToBech32($pk);

        return $this->response(compact('sk', 'pk', 'nsec', 'npub'));
    }

    public function fromSecretKey(#[\SensitiveParameter] string $sk): Response
    {
        $pk = $this->key->getPublicKey($sk);
        $nsec = $this->key->convertPrivateKeyToBech32($sk);
        $npub = $this->key->convertPublicKeyToBech32($pk);

        return $this->response(compact('sk', 'pk', 'nsec', 'npub'));
    }

    public function fromNsec(#[\SensitiveParameter] string $nsec): Response
    {
        $sk = $this->key->convertToHex($nsec);
        $pk = $this->key->getPublicKey($sk);
        $npub = $this->key->convertPublicKeyToBech32($pk);

        return $this->response(compact('sk', 'pk', 'nsec', 'npub'));
    }

    public function fromPublicKey(string $pk): Response
    {
        $npub = $this->key->convertPublicKeyToBech32($pk);

        return $this->response(compact('pk', 'npub'));
    }

    public function fromNpub(string $npub): Response
    {
        $pk = $this->key->convertToHex($npub);

        return $this->response(compact('pk', 'npub'));
    }
}
