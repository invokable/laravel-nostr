<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

class PublishedAtTag implements Arrayable
{
    public function __construct(
        protected readonly int|string $published_at,
    ) {}

    public static function make(int|string $published_at): static
    {
        return new static(published_at: $published_at);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return ['published_at', (string) $this->published_at];
    }
}
