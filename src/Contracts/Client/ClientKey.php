<?php

namespace Revolution\Nostr\Contracts\Client;

use Illuminate\Http\Client\Response;

interface ClientKey
{
    public function generate(): Response;

    public function fromSecretKey(string $sk): Response;

    public function fromNsec(string $nsec): Response;

    public function fromPublicKey(string $pk): Response;

    public function fromNpub(string $npub): Response;
}
