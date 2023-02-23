<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

/**
 * NIP-40.
 */
class ExpirationTag implements Arrayable
{
    public function __construct(
        protected readonly int $expiration,
    ) {
    }

    public static function make(int $expiration): static
    {
        return new static(expiration: $expiration);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['expiration', (string) $this->expiration];
    }
}
