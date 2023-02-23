<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

/**
 * NIP-1.
 */
class EventTag implements Arrayable
{
    public function __construct(
        protected readonly string $id,
        protected readonly string $relay = '',
        protected readonly string $marker = '',
    ) {
    }

    public static function make(
        string $id,
        string $relay = '',
        string $marker = '',
    ): static {
        return new static(...func_get_args());
    }

    /**
     * @return array<string, string, string, string>
     */
    public function toArray(): array
    {
        return ['e', $this->id, $this->relay, $this->marker];
    }
}
