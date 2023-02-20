<?php
declare(strict_types=1);

namespace Revolution\Nostr\Client;

use Illuminate\Http\Client\Response;
use Revolution\Nostr\Client\Concerns\HasHttp;

class PendingKey
{
    use HasHttp;

    public function generate(): Response
    {
        return $this->http()->get('key/generate');
    }

    public function fromSecretKey(string $sk): Response
    {
        return $this->http()->get('key/from_sk', [
            'sk' => $sk,
        ]);
    }

    public function fromNsec(string $nsec): Response
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
