<?php

declare(strict_types=1);

namespace Revolution\Nostr\Notifications;

class NostrRoute
{
    public function __construct(
        public readonly string $sk,
        public ?array $relays = null,
    ) {
    }

    public static function to(string $sk, ?array $relays = null): static
    {
        return new static(...func_get_args());
    }
}
