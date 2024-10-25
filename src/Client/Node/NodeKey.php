<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Node;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Node\Concerns\HasHttp;
use Revolution\Nostr\Contracts\Client\ClientKey;

class NodeKey implements ClientKey
{
    use HasHttp;
    use Macroable;

    public function generate(): Response
    {
        return $this->http()->get('key/generate');
    }

    public function fromSecretKey(#[\SensitiveParameter] string $sk): Response
    {
        return $this->http()->get('key/from_sk', [
            'sk' => $sk,
        ]);
    }

    public function fromNsec(#[\SensitiveParameter] string $nsec): Response
    {
        return $this->http()->get('key/from_nsec', [
            'nsec' => $nsec,
        ]);
    }

    public function fromPublicKey(string $pk): Response
    {
        return $this->http()->get('key/from_pk', [
            'pk' => $pk,
        ]);
    }

    public function fromNpub(string $npub): Response
    {
        return $this->http()->get('key/from_npub', [
            'npub' => $npub,
        ]);
    }
}
