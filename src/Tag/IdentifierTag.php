<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tag;

use Illuminate\Contracts\Support\Arrayable;

class IdentifierTag implements Arrayable
{
    public function __construct(
        protected readonly string $d,
    ) {
    }

    public static function make(
        string $d,
    ): static {
        return new static(d: $d);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['d', $this->d];
    }
}
