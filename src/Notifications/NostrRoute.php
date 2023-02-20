<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

use Illuminate\Contracts\Support\Arrayable;

class NostrRoute implements Arrayable
{
    public function __construct(
        public readonly string $sk,
        public readonly string $relay,
    ) {
    }

    public static function to(string $sk, string $relay): static
    {
        return new static(sk: $sk, relay: $relay);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
