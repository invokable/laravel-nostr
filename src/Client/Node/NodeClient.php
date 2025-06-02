<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Node;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\NativeNip05;
use Revolution\Nostr\Client\Native\NativeRelay;
use Revolution\Nostr\Contracts\NostrDriver;

/**
 * Basic Nostr client. Works with WebAPI.
 */
class NodeClient implements NostrDriver
{
    use Conditionable;
    use Macroable;

    public function key(): NodeKey
    {
        return Container::getInstance()->make(NodeKey::class);
    }

    public function event(): NodeEvent
    {
        return Container::getInstance()->make(NodeEvent::class);
    }

    public function pool(): NodePool
    {
        return Container::getInstance()->make(NodePool::class);
    }

    public function relay(): NativeRelay
    {
        return Container::getInstance()->make(NativeRelay::class);
    }

    public function nip04(): NodeNip04
    {
        return Container::getInstance()->make(NodeNip04::class);
    }

    public function nip05(): NativeNip05
    {
        return Container::getInstance()->make(NativeNip05::class);
    }

    public function nip19(): NodeNip19
    {
        return Container::getInstance()->make(NodeNip19::class);
    }
}
