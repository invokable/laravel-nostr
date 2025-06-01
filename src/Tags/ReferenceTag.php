<?php

declare(strict_types=1);

namespace Revolution\Nostr\Tags;

use Illuminate\Contracts\Support\Arrayable;

class ReferenceTag implements Arrayable
{
    public function __construct(
        protected readonly string $r,
    ) {}

    public static function make(string $r): static
    {
        return new static(r: $r);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function toArray(): array
    {
        return ['r', $this->r];
    }
}
