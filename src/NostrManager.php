<?php

namespace Revolution\Nostr;

use Illuminate\Support\Manager;
use Revolution\Nostr\Client\NostrClient;
use Revolution\Nostr\Contracts\NostrDriver;
use Revolution\Nostr\Contracts\NostrFactory;

class NostrManager extends Manager implements NostrFactory
{
    public function getDefaultDriver(): string
    {
        return 'node';
    }

    protected function createNodeDriver(): NostrDriver
    {
        return $this->container->make(NostrClient::class);
    }
}
