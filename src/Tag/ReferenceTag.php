<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tag;

use Illuminate\Contracts\Support\Arrayable;

/**
 * NIP-12.
 */
class ReferenceTag implements Arrayable
{
    public function __construct(
        protected readonly string $r,
    ) {
    }

    public static function make(string $r): static
    {
        return new static(r: $r);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['r', $this->r];
    }
}
