<?php

namespace Revolution\Nostr;

use Illuminate\Support\Manager;
use Revolution\Nostr\Client\Node\NodeClient;
use Revolution\Nostr\Contracts\NostrDriver;
use Revolution\Nostr\Client\Native\NativeClient;

class NostrManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return 'node';
    }

    protected function createNodeDriver(): NostrDriver
    {
        return $this->container->make(NodeClient::class);
    }

    protected function createNativeDriver(): NostrDriver
    {
        return $this->container->make(NativeClient::class);
    }

    public function node(): NodeClient
    {
        return $this->driver('node');
    }

    public function native(): NativeClient
    {
        return $this->driver('native');
    }
}
