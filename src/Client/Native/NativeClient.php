<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use Illuminate\Container\Container;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Contracts\NostrDriver;
use RuntimeException;

/**
 * Basic Nostr client. PHP native.
 */
class NativeClient implements NostrDriver
{
    use Macroable;
    use Conditionable;

    public function key(): NativeKey
    {
        return Container::getInstance()->make(NativeKey::class);
    }

    public function event(): NativeEvent
    {
        return Container::getInstance()->make(NativeEvent::class);
    }

    public function pool(): NativePool
    {
        return Container::getInstance()->make(NativePool::class);
    }

    public function nip04()
    {
        throw new RuntimeException('Native driver does not support nip04.');
    }

    public function nip05(): NativeNip05
    {
        return Container::getInstance()->make(NativeNip05::class);
    }

    public function nip19()
    {
        throw new RuntimeException('Native driver does not support nip19.');
    }
}
