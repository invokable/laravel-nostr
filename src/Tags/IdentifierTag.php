<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

/**
 * NIP-33.
 */
class IdentifierTag implements Arrayable
{
    /**
     * @param  string  $d  identifier
     */
    public function __construct(
        protected readonly string $d,
    ) {
    }

    /**
     * @param  string  $d  identifier
     */
    public static function make(string $d): static
    {
        return new static(d: $d);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return ['d', $this->d];
    }
}
